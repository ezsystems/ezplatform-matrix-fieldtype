<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL;

use eZ\Publish\SPI\FieldType\Value;
use EzSystems\EzPlatformGraphQL\GraphQL\Mutation\InputHandler\FieldTypeInputHandler;
use Ibexa\FieldTypeMatrix\FieldType\Value as MatrixValue;

class InputHandler implements FieldTypeInputHandler
{
    public function toFieldValue($input, $inputFormat = null): Value
    {
        return new MatrixValue(
            array_map(
                static function (array $row) {
                    return new MatrixValue\Row($row);
                },
                $input
            )
        );
    }
}

class_alias(InputHandler::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\InputHandler');
