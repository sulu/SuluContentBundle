<?php

declare(strict_types=1);

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\ContentBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SuluContentExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig(
            'sulu_admin',
            [
                'forms' => [
                    'directories' => [
                        __DIR__ . '/../Resources/config/forms',
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'doctrine',
            [
                'orm' => [
                    'mappings' => [
                        'SuluContentBundle' => [
                            'type' => 'xml',
                            'prefix' => 'Sulu\Bundle\ContentBundle\Model',
                            'dir' => 'Resources/config/doctrine',
                            'alias' => 'SuluContentBundle',
                            'is_bundle' => true,
                            'mapping' => true,
                        ],
                    ],
                ],
            ]
        );

        $container->prependExtensionConfig(
            'jms_serializer',
            [
                'metadata' => [
                    'directories' => [
                        [
                            'name' => 'SuluContentBundle',
                            'path' => __DIR__ . '/../Resources/config/serializer',
                            'namespace_prefix' => 'Sulu\\Bundle\\ContentBundle\\Model',
                        ],
                    ],
                ],
            ]
        );
    }
}
