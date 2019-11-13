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
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

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
        $loader->load('resolver.xml');
        $loader->load('services.xml');
        $loader->load('handlers.xml');

        // We can not prepend the message bus in framework bundle as we don't
        // want that it is accidently the default bus of a project.
        // So we create the bus here ourselfs be reimplmenting the logic of
        // the FrameworkExtension.
        // See: https://github.com/symfony/symfony/blob/v4.3.6/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L1647-L1686
        $container->register('sulu_content.message_bus', MessageBus::class)
            ->addArgument([])
            ->addTag('messenger.bus');

        $middleware = [
            ['id' => 'add_bus_name_stamp_middleware', 'arguments' => ['sulu_directory.message_bus']],
            ['id' => 'reject_redelivered_message_middleware'],
            ['id' => 'dispatch_after_current_bus'],
            ['id' => 'failed_message_processing_middleware'],
            ['id' => 'send_message'],
            ['id' => 'handle_message'],
        ];

        $container->setParameter('sulu_content.message_bus.middleware', $middleware);

        $container->registerAliasForArgument('sulu_content.message_bus', MessageBusInterface::class);
    }
}
