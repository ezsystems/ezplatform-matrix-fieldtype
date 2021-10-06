<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\FieldType\FieldType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Value as FieldTypeValue;
use eZ\Publish\SPI\FieldType\Value as SPIValue;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;

class Type extends FieldType
{
    /**
     * @inheritdoc
     */
    protected $settingsSchema = [
        'minimum_rows' => [
            'type' => 'integer',
            'default' => 1,
        ],
        'columns' => [
            'type' => 'hash',
            'default' => [],
        ],
    ];

    /** @var string */
    private $fieldTypeIdentifier;

    /**
     * @param string $fieldTypeIdentifier
     */
    public function __construct(string $fieldTypeIdentifier)
    {
        $this->fieldTypeIdentifier = $fieldTypeIdentifier;
    }

    /**
     * @inheritdoc
     */
    protected function getSortInfo(FieldTypeValue $value)
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function validateFieldSettings($fieldSettings): array
    {
        $minimumRows = $fieldSettings['minimum_rows'];
        $columns = array_values($fieldSettings['columns'] ?? []);

        if (!is_numeric($minimumRows) || $minimumRows < 0) {
            $errors[] = new ValidationError(
                'Value must be numeric positive numeric.',
                null,
                [],
                'minimum_rows'
            );
        }

        foreach ($columns as $index => $column) {
            $trimmedIdentifier = trim($column['identifier'] ?? '');

            if (empty($trimmedIdentifier)) {
                $errors[] = new ValidationError(
                    'Column (index: %index%) must have identifier.',
                    null,
                    ['%index%' => $index]
                );
            } else {
                if (in_array($trimmedIdentifier, $identifiers ?? [])) {
                    $errors[] = new ValidationError(
                        'Identifier "%identifier%" must be unique.',
                        null,
                        ['%identifier%' => $trimmedIdentifier]
                    );
                } else {
                    $identifiers[] = $trimmedIdentifier;
                }
            }
        }

        return $errors ?? [];
    }

    /**
     * @inheritdoc
     */
    protected function createValueFromInput($inputValue)
    {
        if (is_array($inputValue)) {
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * @inheritdoc
     */
    public function getFieldTypeIdentifier(): string
    {
        return $this->fieldTypeIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getEmptyValue(): SPIValue
    {
        $value = new Value([]);

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function fromHash($hash): SPIValue
    {
        $entries = $hash['entries'] ?? [];

        foreach ($entries as $row) {
            $rows[] = new Row($row);
        }

        return new Value($rows ?? []);
    }

    /**
     * @inheritdoc
     */
    protected function checkValueStructure(FieldTypeValue $value)
    {
        // Value is self-contained and strong typed
        return;
    }

    /**
     * @inheritdoc
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value $value */
        return $value->getRows()->count() === 0;
    }

    /**
     * @inheritdoc
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value)
    {
        if ($this->isEmptyValue($value)) {
            return [];
        }

        $countNonEmptyRows = 0;

        /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value $value */
        foreach ($value->getRows() as $row) {
            if (!$row->isEmpty()) {
                ++$countNonEmptyRows;
            }
        }

        if ($countNonEmptyRows < $fieldDefinition->fieldSettings['minimum_rows']) {
            $validationErrors[] = new ValidationError(
                'Matrix must contain at least %minimum_rows% non-empty rows.',
                null,
                [
                    '%minimum_rows%' => $fieldDefinition->fieldSettings['minimum_rows'],
                ],
                $fieldDefinition->getName()
            );
        }

        return $validationErrors ?? [];
    }

    /**
     * @inheritdoc
     */
    public function toHash(SPIValue $value)
    {
        /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value $value */
        $rows = $value->getRows();

        $hash['entries'] = [];

        foreach ($rows as $row) {
            $hash['entries'][] = $row->getCells();
        }

        return $hash;
    }

    public function isSearchable(): bool
    {
        return true;
    }
}

class_alias(Type::class, 'EzSystems\EzPlatformMatrixFieldtype\FieldType\Type');
