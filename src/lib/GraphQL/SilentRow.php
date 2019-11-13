<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */


namespace EzSystems\EzPlatformMatrixFieldtype\GraphQL;

use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row;

class SilentRow extends Row
{
    public function __get($name)
    {
        return $this->cells[$name] ?? '';
    }
}
