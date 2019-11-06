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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\DependencyInjection;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\DoctrineExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Sulu\Bundle\AdminBundle\DependencyInjection\SuluAdminExtension;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\MetadataLoader;
use Sulu\Bundle\ContentBundle\DependencyInjection\SuluContentExtension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Infrastructure\Doctrine\DimensionRepository;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SuluContentExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions(): array
    {
        return [
            $this->getExtension(),
        ];
    }

    protected function getExtension(): SuluContentExtension
    {
        return new SuluContentExtension();
    }

    public function testLoad(): void
    {
        $this->load();
        $this->assertContainerBuilderHasService('sulu_content.metadata_loader', MetadataLoader::class);
        $this->assertContainerBuilderHasService('sulu.repository.dimension', DimensionRepository::class);
        $this->assertContainerBuilderHasParameter('sulu.model.dimension.class', Dimension::class);
    }

    public function testLoadObjects(): void
    {
        $this->load([
            'objects' => [
                'dimension' => [
                    'model' => 'TestModel',
                    'repository' => 'TestRepository',
                ],
            ],
        ]);

        $this->assertContainerBuilderHasParameter('sulu.model.dimension.class', 'TestModel');
        $this->assertContainerBuilderHasService('sulu.repository.dimension', 'TestRepository');
    }

    public function testPrepend(): void
    {
        $extension = $this->getExtension();
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $doctrineExtension = new DoctrineExtension();
        $containerBuilder->registerExtension($doctrineExtension);

        $doctrineExtension = new SuluAdminExtension();
        $containerBuilder->registerExtension($doctrineExtension);
        $extension->prepend($containerBuilder);

        $this->assertSame([
            [
                'orm' => [
                    'mappings' => [
                        'SuluContentBundleDimension' => [
                            'type' => 'xml',
                            'prefix' => 'Sulu\Bundle\ContentBundle\Dimension\Domain\Model',
                            'dir' => \dirname(\dirname(\dirname(__DIR__))) . '/Resources/config/doctrine/Dimension',
                            'alias' => 'SuluDirectoryBundle',
                            'is_bundle' => false,
                            'mapping' => true,
                        ],
                    ],
                ],
            ],
        ], $containerBuilder->getExtensionConfig('doctrine'));

        $this->assertSame([
            [
                'forms' => [
                    'directories' => [
                        \dirname(\dirname(\dirname(__DIR__))) . '/Resources/config/forms',
                    ],
                ],
            ],
        ], $containerBuilder->getExtensionConfig('sulu_admin'));
    }
}
