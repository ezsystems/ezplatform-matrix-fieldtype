<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtype\FieldType;

use eZ\Publish\Core\FieldType\Value as BaseValue;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection;

class Value extends BaseValue
{
    /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection */
    protected $rows;

    /**
     * @param array $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = new RowsCollection($rows);
    }

    /**
     * @return \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection
     */
    public function getRows(): RowsCollection
    {
        return $this->rows;
    }

    /**
     * @param \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection $rows
     */
    public function setRows(RowsCollection $rows): void
    {
        $this->rows = $rows;
    }

    /**
     * Returns a string representation of the field value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return '';
    }
}
