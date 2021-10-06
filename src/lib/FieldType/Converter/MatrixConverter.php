<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Converter;

use eZ\Publish\Core\FieldType\FieldSettings;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;

class MatrixConverter implements Converter
{
    /**
     * Converts data from $value to $storageFieldValue.
     * Note: You should not throw on validation here, as it is implicitly used by ContentService->createContentDraft().
     *       Rather allow invalid value or omit it to let validation layer in FieldType handle issues when user tried
     *       to publish the given draft.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void
    {
        $entries = $value->data['entries'] ?? [];

        $storageFieldValue->dataText = json_encode(array_values($entries));
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void
    {
        $fieldValue->data = [
            'entries' => $value->dataText === null ? [] : json_decode($value->dataText, true),
        ];
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;

        $columns = array_values($fieldSettings['columns']);
        $minimumRows = (int)$fieldSettings['minimum_rows'];

        array_walk($columns, static function ($column) {
            return [
                'identifier' => trim($column['identifier'] ?? ''),
                'name' => trim($column['name'] ?? ''),
            ];
        });

        $storageDef->dataInt1 = $minimumRows;
        $storageDef->dataText5 = json_encode($columns);
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'minimum_rows' => (int)$storageDef->dataInt1,
                'columns' => json_decode($storageDef->dataText5 ?? '[]', true),
            ]
        );

        $fieldDef->defaultValue->data = [];
    }

    /**
     * Returns the name of the index column in the attribute table.
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     * If the indexing is not supported, this method must return false.
     *
     * @return string|false
     */
    public function getIndexColumn()
    {
        return false;
    }
}

class_alias(MatrixConverter::class, 'EzSystems\EzPlatformMatrixFieldtype\FieldType\Converter\MatrixConverter');
