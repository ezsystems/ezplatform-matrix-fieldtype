<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\FieldTypeMatrix\Form\Type\FieldType;

use Ibexa\FieldTypeMatrix\FieldType\Value\Row;
use Ibexa\FieldTypeMatrix\FieldType\Value\RowsCollection;
use Ibexa\FieldTypeMatrix\Form\Transformer\FieldTypeModelTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatrixFieldType extends AbstractType
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
        return 'ezplatform_fieldtype_ezmatrix';
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['columns', 'minimum_rows']);
        $resolver->addAllowedTypes('columns', 'array');
        $resolver->addAllowedTypes('minimum_rows', 'integer');
        $resolver->setDefault('translation_domain', 'matrix_fieldtype');
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
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('entries', MatrixCollectionType::class, [
                'columns' => $options['columns'],
                'minimum_rows' => $options['minimum_rows'],
                'entry_options' => [
                    'columns' => $options['columns'],
                ],
            ]);

        $columnsByIdentifier = array_flip(array_column($options['columns'], 'identifier'));

        // Filter out unnecessary/obsolete columns data
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($columnsByIdentifier) {
            $value = $event->getData();

            /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row $originalRow */
            foreach ($value->getRows() as $originalRow) {
                $cells = $originalRow->getCells();
                $rows[] = new Row(array_intersect_key($cells, $columnsByIdentifier));
            }

            $value->setRows(new RowsCollection($rows ?? []));
        });

        $builder->addModelTransformer(new FieldTypeModelTransformer());
    }
}

class_alias(MatrixFieldType::class, 'EzSystems\EzPlatformMatrixFieldtype\Form\Type\FieldType\MatrixFieldType');
