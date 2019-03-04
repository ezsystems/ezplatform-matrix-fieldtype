<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\EzPlatformMatrixFieldtype\Form\Type\FieldType;

use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row;
use EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\RowsCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($columnsByIdentifier) {
            $value = $event->getData();

            /** @var \EzSystems\EzPlatformMatrixFieldtype\FieldType\Value\Row $originalRow */
            foreach ($value->getRows() as $originalRow) {
                $cells = $originalRow->getCells();
                $rows[] = new Row(array_intersect_key($cells, $columnsByIdentifier));
            }

            $value->setRows(new RowsCollection($rows ?? []));
        });

        $builder->addModelTransformer(new class() implements DataTransformerInterface {
            /**
             * Transforms a value from the original representation to a transformed representation.
             *
             * @param mixed $value The value in the original representation
             *
             * @return mixed The value in the transformed representation
             *
             * @throws TransformationFailedException when the transformation fails
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
             * @throws TransformationFailedException when the transformation fails
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
        });
    }
}
