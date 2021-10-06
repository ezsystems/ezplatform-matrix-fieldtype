<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\FieldType\Value;

class Row
{
    protected $cells;

    /**
     * Row constructor.
     *
     * @param array $cells
     */
    public function __construct(array $cells = [])
    {
        $this->cells = $cells;
    }

    /**
     * @return array
     */
    public function getCells(): array
    {
        return $this->cells;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        $trimmed = array_map('trim', $this->cells);
        $filtered = array_filter($trimmed, 'strlen');

        return count($filtered) === 0;
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->cells[$name];
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name): bool
    {
        return isset($this->cells[$name]);
    }
}

class_alias(Row::class, 'EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row');
