<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtype\FieldType;

use eZ\Publish\SPI\FieldType\Indexable as IndexableInterface;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search;

final class Indexable implements IndexableInterface
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        $entries = $field->value->data['entries'] ?? [];

        $cells = [];
        foreach ($entries as $entry) {
            foreach ($entry as $column => $value) {
                $cells[] = $value;
            }
        }

        return [
            new Search\Field(
                'fulltext',
                implode(' ', $cells),
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    public function getIndexDefinition(): array
    {
        return [];
    }

    public function getDefaultMatchField(): ?string
    {
        return null;
    }

    public function getDefaultSortField(): ?string
    {
        return null;
    }
}
