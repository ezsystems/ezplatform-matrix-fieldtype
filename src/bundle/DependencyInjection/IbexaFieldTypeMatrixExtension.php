<?php

/**
 * @copyright Copyright (C) Ibexa AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace Ibexa\Bundle\FieldTypeMatrix\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class IbexaFieldTypeMatrixExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array $configs
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('default_parameters.yaml');
        $loader->load('services.yaml');
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $this->prependKernelSettings($container);
        $this->prependJMSTranslation($container);
        $this->prependGraphQL($container);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prependKernelSettings(ContainerBuilder $container)
    {
        $configFile = __DIR__ . '/../Resources/config/kernel.yaml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function prependJMSTranslation(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('jms_translation', [
            'configs' => [
                'ezplatform_matrix_fieldtype' => [
                    'dirs' => [
                        __DIR__ . '/../../',
                    ],
                    'output_dir' => __DIR__ . '/../Resources/translations/',
                    'output_format' => 'xliff',
                    'excluded_dirs' => ['Behat', 'Tests', 'node_modules'],
                    'extractors' => [],
                ],
            ],
        ]);
    }

    private function prependGraphQL(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('overblog_graphql', [
            'definitions' => [
                'mappings' => [
                    'types' => [
                        [
                            'type' => 'yaml',
                            'dir' => __DIR__ . '/../Resources/config/graphql/types',
                        ],
                    ],
                ],
            ],
        ]);
    }
}

class_alias(IbexaFieldTypeMatrixExtension::class, 'EzSystems\EzPlatformMatrixFieldtypeBundle\DependencyInjection\EzPlatformMatrixFieldtypeExtension');
