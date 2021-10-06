<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

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
