<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtypeBundle\Command;

use Doctrine\DBAL\Connection;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Converter\MatrixConverter;
use SimpleXMLElement;
use Symfony\Component\Console\Command\Command;
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

    /** @var string */
    private $contentclassAttributeSchema;

    /** @var string */
    private $contentobjectAttributeSchema;

    /**
     * @param \Doctrine\DBAL\Connection $connection
     * @param string $contentclassAttributeSchema
     * @param string $contentobjectAttributeSchema
     */
    public function __construct(
        Connection $connection,
        string $contentclassAttributeSchema,
        string $contentobjectAttributeSchema
    ) {
        $this->connection = $connection;
        $this->contentclassAttributeSchema = $contentclassAttributeSchema;
        $this->contentobjectAttributeSchema = $contentobjectAttributeSchema;

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

        $iterationCount = (int)$input->getOption('iteration-count');
        $converter = new MatrixConverter();

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

        libxml_use_internal_errors(true);

        foreach ($query->execute()->fetchAll() as $attribute) {
            $io->comment(sprintf('Migrate %s:%s attribute.', $attribute['contenttype_identifier'], $attribute['identifier']));

            $xml = simplexml_load_string($attribute['columns']);

            if (!$xml) {
                $io->warning('Attribute definition is not an XML.');
            }

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
                'minimum_rows' => $attribute['minimum_rows'],
                'columns' => array_values($columns),
            ];

            $converter->toStorageFieldDefinition($fieldDefinition, $storageFieldDefinition);
            dump($storageFieldDefinition);

            $query = $this->connection->createQueryBuilder();
            $query
                ->update('ezcontentclass_attribute', 'attr')
                ->set('attr.data_int1', ':minimum_rows')
                ->set('attr.data_text5', ':columns')
                ->where('attr.id = :id')
                ->setParameter(':id', $attribute['id'])
                ->setParameter(':mu', $attribute['id']);

            dump($query->getSQL()); die();


            die();
        }


        /*
        for ($offset = 0; $offset <= $locationsCount; $offset += $iterationCount) {
            gc_disable();
            $locations = $this->repository->sudo(
                function (Repository $repository) use ($offset, $iterationCount) {
                    return $repository->getLocationService()->loadAllLocations($offset, $iterationCount);
                }
            );
            $this->processLocations($locations, $progressBar);
            gc_enable();
        }
        */
    }
}