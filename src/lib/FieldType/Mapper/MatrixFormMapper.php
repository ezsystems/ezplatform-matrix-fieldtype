<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtype\FieldType\Mapper;

use EzSystems\EzPlatformMatrixFieldtype\Form\Type\ColumnType;
use EzSystems\EzPlatformMatrixFieldtype\Form\Type\FieldType\MatrixFieldType;
use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;

class MatrixFormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * "Maps" FieldDefinition form to current FieldType.
     * Gives the opportunity to enrich $fieldDefinitionForm with custom fields for:sM,
     * - validator configuration,
     * - field settings
     * - default value.
     *
     * @param FormInterface $fieldDefinitionForm form for current FieldDefinition
     * @param FieldDefinitionData $data underlying data for current FieldDefinition form
     */
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $isTranslation = $data->contentTypeData->languageCode !== $data->contentTypeData->mainLanguageCode;
        $fieldDefinitionForm
            ->add('minimum_rows', IntegerType::class, [
                'required' => false,
                'property_path' => 'fieldSettings[minimum_rows]',
                'label' => /** @Desc("Minimum number of rows") */ 'field_definition.ezmatrix.minimum_rows',
                'translation_domain' => 'matrix_fieldtype',
                'disabled' => $isTranslation,
            ])
            ->add('columns', CollectionType::class, [
                'entry_type' => ColumnType::class,
                'entry_options' => ['required' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'delete_empty' => false,
                'prototype' => true,
                'prototype_name' => '__number__',
                'required' => false,
                'property_path' => 'fieldSettings[columns]',
                'label' => /** @Desc("Columns") */ 'field_definition.ezmatrix.columns',
                'translation_domain' => 'matrix_fieldtype',
                'disabled' => $isTranslation,
            ]);
    }

    /**
     * Maps Field form to current FieldType.
     * Allows to add form fields for content edition.
     *
     * @param FormInterface $fieldForm form for the current Field
     * @param FieldData $data underlying data for current Field form
     */
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MatrixFieldType::class, [
                            'label' => $fieldDefinition->getName(),
                            'required' => $fieldDefinition->isRequired,
                            'columns' => $fieldDefinition->fieldSettings['columns'],
                            'minimum_rows' => $fieldDefinition->fieldSettings['minimum_rows'],
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
