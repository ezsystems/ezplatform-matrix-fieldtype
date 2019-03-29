<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtypeBundle\Command;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Converter\MatrixConverter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateLegacyMatrixCommand extends Command
{
    private const DEFAULT_ITERATION_COUNT = 1000;

    private const EZMATRIX_IDENTIFIER = 'ezmatrix';

    protected static $defaultName = 'ezplatform:migrate:legacy_matrix';

    /** @var \Doctrine\DBAL\Connection */
    private $connection;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     */
    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'iteration-count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'Number of matrix FieldType values fetched into memory and processed at once',
                self::DEFAULT_ITERATION_COUNT
            );
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->comment('Migrating legacy ezmatrix fieldtype');

        $iterationCount = (int)$input->getOption('iteration-count');
        $converter = new MatrixConverter();

        $contentClassAttributes = $this->getContentClassAttributes();

        libxml_use_internal_errors(true);

        foreach ($contentClassAttributes as $contentClassAttribute) {
            $io->comment(sprintf('Migrate %s:%s attribute.', $contentClassAttribute['contenttype_identifier'], $contentClassAttribute['identifier']));

            $xml = simplexml_load_string((string)$contentClassAttribute['columns']);

            if ($xml) {
                $columnList = $xml->xpath('//column-name');

                $columns = [];

                foreach ($columnList as $column) {
                    $columns[(int)$column['idx']] = [
                        'identifier' => (string)$column['id'],
                        'name' => (string)$column,
                    ];
                }

                $fieldDefinition = new FieldDefinition();
                $storageFieldDefinition = new StorageFieldDefinition();

                $fieldDefinition->fieldTypeConstraints->fieldSettings = [
                    'minimum_rows' => $contentClassAttribute['minimum_rows'],
                    'columns' => array_values($columns),
                ];

                $converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition);

                $this->updateContentClassAttribute(
                    (int)$contentClassAttribute['id'],
                    (int)$storageFieldDefinition->dataInt1,
                    (string)$storageFieldDefinition->dataText5
                );

                $columnsJson = $storageFieldDefinition->dataText5;
            } else {
                $columnsJson = $contentClassAttribute['columns'];
            }

            $contentAttributesCount = $this->getContentObjectAttributesCount(
                (int)$contentClassAttribute['id']
            );

            $columns = json_decode($columnsJson);

            $progressBar = $this->getProgressBar($contentAttributesCount, $output);
            $progressBar->start();

            for ($offset = 0; $offset <= $contentAttributesCount; $offset += $iterationCount) {
                gc_disable();

                $contentObjectAttributes = $this->getContentObjectAttributes(
                    (int)$contentClassAttribute['id'],
                    $offset,
                    $iterationCount
                );

                foreach ($contentObjectAttributes as $contentObjectAttribute) {
                    $xml = simplexml_load_string(
                        (string)$contentObjectAttribute['data_text']
                    );

                    $storageFieldValue = new StorageFieldValue();
                    $fieldValue = new FieldValue([
                        'data' => [
                            'entries' => [],
                        ]
                    ]);

                    if (!$xml) {
                        $progressBar->advance(1);

                        continue;
                    }

                    $rows = $this->convertCellsToRows($xml->xpath('c'), $columns);

                    $fieldValue->data['entries'] = $rows;

                    $converter->toStorageValue($fieldValue, $storageFieldValue);

                    $this->updateContentObjectAttribute(
                        (int)$contentObjectAttribute['id'],
                        (string)$storageFieldValue->dataText
                    );

                    $progressBar->advance(1);
                }

                gc_enable();
            }

            $progressBar->finish();

            $output->writeln(['', '']);
        }

        $io->success('Done.');
    }

    /**
     * @param array $cells
     * @param array $columns
     *
     * @return array
     */
    private function convertCellsToRows(array $cells, array $columns): array
    {
        $rows = [];
        $columnsCount = count($columns);

        foreach ($cells as $index => $cell) {
            $columnIndex = $index % $columnsCount;
            $columnIdentifier = $columns[$columnIndex]->identifier;

            $row[$columnIdentifier] = (string)$cell;

            if ($columnIndex === $columnsCount - 1) {
                $rows[] = $row;
                $row = [];
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    private function getContentClassAttributes(): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select([
                'attr.id',
                'attr.identifier',
                'attr.data_int1 as minimum_rows',
                'attr.data_text5 as columns',
                'class.identifier as contenttype_identifier',
            ])
            ->from('ezcontentclass_attribute', 'attr')
            ->join('attr', 'ezcontentclass', 'class', 'class.id = attr.contentclass_id')
            ->where('attr.data_type_string = :identifier')
            ->setParameter(':identifier', self::EZMATRIX_IDENTIFIER);

        return $query->execute()->fetchAll();
    }

    /**
     * @param int $id
     * @param int $minimumRows
     * @param string $columns
     */
    private function updateContentClassAttribute(int $id, int $minimumRows, string $columns): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentclass_attribute', 'attr')
            ->set('attr.data_int1', ':minimum_rows')
            ->set('attr.data_text5', ':columns')
            ->where('attr.id = :id')
            ->setParameter(':id', $id)
            ->setParameter(':minimum_rows', $minimumRows)
            ->setParameter(':columns', $columns);

        $query->execute();
    }

    /**
     * @param int $id
     *
     * @return int
     */
    private function getContentObjectAttributesCount(int $id): int
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select('count(1)')
            ->from('ezcontentobject_attribute', 'attr')
            ->where('attr.contentclassattribute_id = :class_attr_id')
            ->setParameter(':class_attr_id', $id);

        return (int)$query->execute()->fetchColumn(0);
    }

    /**
     * @param int $id
     * @param int $offset
     * @param int $iterationCount
     *
     * @return array
     */
    private function getContentObjectAttributes(int $id, int $offset, int $iterationCount): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(['id', 'data_text'])
            ->from('ezcontentobject_attribute', 'attr')
            ->where('attr.contentclassattribute_id = :class_attr_id')
            ->setParameter(':class_attr_id', $id)
            ->setFirstResult($offset)
            ->setMaxResults($iterationCount);

        return $query->execute()->fetchAll();
    }

    /**
     * @param int $id
     * @param string $rows
     */
    private function updateContentObjectAttribute(int $id, string $rows): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->update('ezcontentobject_attribute', 'attr')
            ->set('attr.data_text', ':rows')
            ->where('attr.id = :id')
            ->setParameter(':id', $id)
            ->setParameter(':rows', $rows);

        $query->execute();
    }

    /**
     * @param int $maxSteps
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Symfony\Component\Console\Helper\ProgressBar
     */
    protected function getProgressBar(int $maxSteps, OutputInterface $output): ProgressBar
    {
        $progressBar = new ProgressBar($output, $maxSteps);
        $progressBar->setFormat(
            ' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%'
        );

        return $progressBar;
    }
}