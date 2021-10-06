<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use eZ\Publish\API\Repository\Values\Content\Content;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;

class FieldValueResolver
{
    public function resolveMatrixFieldValue(Content $content, string $fieldDefIdentifier): RowsCollection
    {
        $silentRows = [];

        /** @var \Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection $rows $rows */
        $rows = $content->getFieldValue($fieldDefIdentifier)->getRows();
        foreach ($rows as $row) {
            $silentRows[] = new SilentRow($row->getCells());
        }

        return new RowsCollection($silentRows);
    }
}

class_alias(FieldValueResolver::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\FieldValueResolver');
