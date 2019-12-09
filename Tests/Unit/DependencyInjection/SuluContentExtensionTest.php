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
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacadeInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\DimensionRepository;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\MetadataLoader;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilderInterface;
use Sulu\Bundle\ContentBundle\DependencyInjection\SuluContentExtension;
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

        // Test persistence bundle registered service and parameters
        $this->assertContainerBuilderHasService('sulu.repository.dimension', DimensionRepository::class);
        $this->assertContainerBuilderHasParameter('sulu.model.dimension.class', Dimension::class);

        // Main services aliases
        $this->assertContainerBuilderHasAlias(ContentFacadeInterface::class, 'sulu_content.content_facade');
        $this->assertContainerBuilderHasAlias(ContentLoaderInterface::class, 'sulu_content.content_loader');
        $this->assertContainerBuilderHasAlias(ContentPersisterInterface::class, 'sulu_content.content_persister');
        $this->assertContainerBuilderHasAlias(ApiViewResolverInterface::class, 'sulu_content.api_view_resolver');
        $this->assertContainerBuilderHasAlias(ContentCopierInterface::class, 'sulu_content.content_copier');
        $this->assertContainerBuilderHasAlias(ContentWorkflowInterface::class, 'sulu_content.content_workflow');

        // Additional services aliases
        $this->assertContainerBuilderHasAlias(ContentViewBuilderInterface::class, 'sulu_content.content_view_builder');
        $this->assertContainerBuilderHasAlias(ViewFactoryInterface::class, 'sulu_content.view_factory');
        $this->assertContainerBuilderHasAlias(ContentDimensionCollectionFactoryInterface::class, 'sulu_content.content_dimension_factory');
        $this->assertContainerBuilderHasAlias(ContentDimensionRepositoryInterface::class, 'sulu_content.content_dimension_repository');
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
                            'prefix' => 'Sulu\Bundle\ContentBundle\Content\Domain\Model',
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
