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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactory;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilder;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilderInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistry;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistryInterface;

class ContentViewBuilderTest extends TestCase
{
    protected function getContentViewBuilder(
        PreviewObjectProviderRegistryInterface $previewObjectProviderRegistry = null
    ): ContentViewBuilderInterface {
        if (null === $previewObjectProviderRegistry) {
            $previewObjectProviderRegistry = $this->getPreviewObjectProviderRegistry([]);
        }

        return new ContentViewBuilder(new ViewBuilderFactory(), $previewObjectProviderRegistry);
    }

    /**
     * @param array<string, PreviewObjectProviderInterface> $providers
     */
    protected function getPreviewObjectProviderRegistry(array $providers): PreviewObjectProviderRegistryInterface
    {
        return new PreviewObjectProviderRegistry($providers);
    }

    protected function getContentObjectProvider(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentDataMapperInterface $contentDataMapper,
        string $entityClass
    ): ContentObjectProvider {
        return new ContentObjectProvider(
            $entityManager,
            $contentResolver,
            $contentDataMapper,
            $entityClass
        );
    }

    public function testBuild(): void
    {
        $contentViewBuilder = $this->getContentViewBuilder();

        $viewCollection = new ViewCollection();
        $contentViewBuilder->build(
            $viewCollection,
            'examples',
            'example',
            'edit_parent_key',
            'add_parent_key'
        );

        /** @var FormViewBuilderInterface $editContentProjection */
        $editContentProjection = $viewCollection->get('edit_parent_key.content');
        $editExcerptView = $viewCollection->get('edit_parent_key.excerpt');
        $editSeoView = $viewCollection->get('edit_parent_key.seo');
        $addContentProjection = $viewCollection->get('add_parent_key.content');

        $this->assertCount(4, $viewCollection->all());

        // Test Edit Content View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editContentProjection);
        $this->assertSame('example', $editContentProjection->getView()->getOption('formKey'));

        // Test Edit Excerpt View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editExcerptView);
        $this->assertSame('content_excerpt', $editExcerptView->getView()->getOption('formKey'));

        // Test Edit Seo View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editSeoView);
        $this->assertSame('content_seo', $editSeoView->getView()->getOption('formKey'));

        // Test Add Content View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $addContentProjection);
        $this->assertSame('example', $addContentProjection->getView()->getOption('formKey'));
    }

    public function testBuildWithPreview(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);

        $contentObjectProvider = $this->getContentObjectProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentDataMapper->reveal(),
            Example::class
        );

        $previewObjectProviders = ['preview-examples' => $contentObjectProvider];
        $previewObjectProviderRegistry = $this->getPreviewObjectProviderRegistry($previewObjectProviders);
        $contentViewBuilder = $this->getContentViewBuilder($previewObjectProviderRegistry);

        $viewCollection = new ViewCollection();
        $contentViewBuilder->build(
            $viewCollection,
            'preview-examples',
            'preview-example',
            'edit_parent_key',
            'add_parent_key'
        );

        /** @var FormViewBuilderInterface $editContentProjection */
        $editContentProjection = $viewCollection->get('edit_parent_key.content');
        $editExcerptView = $viewCollection->get('edit_parent_key.excerpt');
        $editSeoView = $viewCollection->get('edit_parent_key.seo');
        $addContentProjection = $viewCollection->get('add_parent_key.content');

        $this->assertCount(4, $viewCollection->all());

        // Test Edit Content View
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $editContentProjection);
        $this->assertSame('preview-example', $editContentProjection->getView()->getOption('formKey'));

        // Test Edit Excerpt View
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $editExcerptView);
        $this->assertSame('content_excerpt', $editExcerptView->getView()->getOption('formKey'));

        // Test Edit Seo View
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $editSeoView);
        $this->assertSame('content_seo', $editSeoView->getView()->getOption('formKey'));

        // Test Add Content View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $addContentProjection);
        $this->assertSame('preview-example', $addContentProjection->getView()->getOption('formKey'));
    }
}
