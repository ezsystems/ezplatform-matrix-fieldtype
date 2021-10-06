<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\GraphQL\Schema;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use EzSystems\EzPlatformGraphQL\Schema\Builder;
use EzSystems\EzPlatformGraphQL\Schema\Worker;

class MatrixFieldDefinitionSchemaWorker implements Worker
{
    /** @var \EzSystems\EzPlatformMatrixFieldtype\GraphQL\Schema\NameHelper */
    private $nameHelper;

    public function __construct(NameHelper $nameHelper)
    {
        $this->nameHelper = $nameHelper;
    }

    public function work(Builder $schema, array $args)
    {
        $typeName = $this->typeName($args);
        $schema->addType(new Builder\Input\Type($typeName, 'object'));

        /** @var \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition */
        $fieldDefinition = $args['FieldDefinition'];
        foreach ($fieldDefinition->getFieldSettings()['columns'] as $column) {
            $schema->addFieldToType(
                $typeName,
                new Builder\Input\Field(
                    $column['identifier'],
                    'String',
                    ['description' => $column['name']]
                ));
        }
    }

    public function canWork(Builder $schema, array $args)
    {
        return
            isset($args['ContentType'])
            && $args['ContentType'] instanceof ContentType
            && isset($args['FieldDefinition'])
            && $args['FieldDefinition'] instanceof FieldDefinition
            && $args['FieldDefinition']->fieldTypeIdentifier === 'ezmatrix'
            && !$schema->hasType($this->typeName($args));
    }

    private function typeName(array $args)
    {
        return $this->nameHelper->matrixFieldDefinitionType($args['ContentType'], $args['FieldDefinition']);
    }
}

class_alias(MatrixFieldDefinitionSchemaWorker::class, 'EzSystems\EzPlatformMatrixFieldtype\GraphQL\Schema\MatrixFieldDefinitionSchemaWorker');
