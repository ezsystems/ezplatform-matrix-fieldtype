<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Transformer;

use Ibexa\FieldTypeMatrix\FieldType\Value;
use Ibexa\FieldTypeMatrix\FieldType\Value\Row;
use Symfony\Component\Form\DataTransformerInterface;

class FieldTypeModelTransformer implements DataTransformerInterface
{
    /**
     * Transforms a value from the original representation to a transformed representation.
     *
     * @param mixed $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException when the transformation fails
     */
    public function transform($value)
    {
        $hash['entries'] = [];

        foreach ($value->getRows() as $row) {
            $hash['entries'][] = $row->getCells();
        }

        return $hash;
    }

    /**
     * Transforms a value from the transformed representation to its original
     * representation.
     *
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws \Symfony\Component\Form\Exception\TransformationFailedException when the transformation fails
     */
    public function reverseTransform($value)
    {
        $entries = $value['entries'] ?? [];

        foreach ($entries as $entry) {
            $row = new Row($entry);

            if (!$row->isEmpty()) {
                $rows[] = $row;
            }
        }

        return new Value($rows ?? []);
    }
}

class_alias(FieldTypeModelTransformer::class, 'EzSystems\EzPlatformMatrixFieldtype\Form\Transformer\FieldTypeModelTransformer');
