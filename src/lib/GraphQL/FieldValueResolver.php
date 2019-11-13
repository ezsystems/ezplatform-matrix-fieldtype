<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace EzSystems\EzPlatformMatrixFieldtype\GraphQL;

use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection;

class FieldValueResolver
{
    public function resolveMatrixFieldValue(Content $content, string $fieldDefIdentifier): RowsCollection
    {
        $silentRows = [];

        /** @var RowsCollection $rows $rows */
        $rows = $content->getFieldValue($fieldDefIdentifier)->getRows();
        foreach ($rows as $row) {
            $silentRows[] = new SilentRow($row->getCells());
        }
        return new RowsCollection($silentRows);
    }
}
