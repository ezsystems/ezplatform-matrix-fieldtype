<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Type\FieldType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatrixCollectionType extends AbstractType
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getBlockPrefix();
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_ezmatrix_collection';
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['columns', 'minimum_rows']);
        $resolver->addAllowedTypes('columns', 'array');
        $resolver->addAllowedTypes('minimum_rows', 'integer');

        $resolver->setDefaults([
            'label' => false,
            'allow_add' => true,
            'allow_delete' => true,
            'entry_type' => MatrixEntryType::class,
            'prototype' => true,
            'prototype_name' => '__index__',
        ]);

        parent::configureOptions($resolver);
    }

    /**
     * @param \Symfony\Component\Form\FormView $view
     * @param \Symfony\Component\Form\FormInterface $form
     * @param array $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['columns'] = $options['columns'];
        $view->vars['minimum_rows'] = $options['minimum_rows'];
    }

    /**
     * @return string
     */
    public function getParent(): string
    {
        return CollectionType::class;
    }
}

class_alias(MatrixCollectionType::class, 'EzSystems\EzPlatformMatrixFieldtype\Form\Type\FieldType\MatrixCollectionType');
