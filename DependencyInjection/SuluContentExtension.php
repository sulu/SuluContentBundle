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

use Sulu\Bundle\PersistenceBundle\DependencyInjection\PersistenceExtensionTrait;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class SuluContentExtension extends Extension implements PrependExtensionInterface
{
    use PersistenceExtensionTrait;

    public function prepend(ContainerBuilder $container)
    {
        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig(
                'doctrine',
                [
                    'orm' => [
                        'mappings' => [
                            'SuluContentBundleDimension' => [
                                'type' => 'xml',
                                'prefix' => 'Sulu\Bundle\ContentBundle\Content\Domain\Model',
                                'dir' => \dirname(__DIR__) . '/Resources/config/doctrine/Dimension',
                                'alias' => 'SuluDirectoryBundle',
                                'is_bundle' => false,
                                'mapping' => true,
                            ],
                        ],
                    ],
                ]
            );
        }

        if ($container->hasExtension('sulu_admin')) {
            $container->prependExtensionConfig(
                'sulu_admin',
                [
                    'forms' => [
                        'directories' => [
                            \dirname(__DIR__) . '/Resources/config/forms',
                        ],
                    ],
                ]
            );
        }
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->configurePersistence($config['objects'], $container);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('mapper.xml');
        $loader->load('merger.xml');
        $loader->load('normalizer.xml');
        $loader->load('services.xml');
    }
}
