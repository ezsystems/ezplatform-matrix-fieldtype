<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace Ibexa\FieldTypeMatrix\GraphQL;

use Ibexa\FieldTypeMatrix\FieldType\Value\Row;

class SilentRow extends Row
{
    public function __get($name)
    {
        return $this->cells[$name] ?? '';
    }
}

class_alias(SilentRow::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\SilentRow');
