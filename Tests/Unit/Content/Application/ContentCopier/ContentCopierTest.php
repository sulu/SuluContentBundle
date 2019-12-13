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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentCopier;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopier;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentCopierTest extends TestCase
{
    protected function createContentCopierInstance(
        ContentLoaderInterface $contentLoader,
        ViewFactoryInterface $viewFactory,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver
    ): ContentCopierInterface {
        return new ContentCopier(
            $contentLoader,
            $viewFactory,
            $contentPersister,
            $contentResolver
        );
    }

    public function testCopy(): void
    {
        $sourceContentView = $this->prophesize(ContentViewInterface::class);
        $targetContentView = $this->prophesize(ContentViewInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentViewFactory = $this->prophesize(ViewFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);

        $contentLoader->load($sourceContentRichEntity->reveal(), $sourceDimensionAttributes)
            ->willReturn($sourceContentView->reveal())
            ->shouldBeCalled();

        $contentResolver->resolve($sourceContentView->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentView->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentLoader->reveal(),
            $contentViewFactory->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $this->assertSame(
            $targetContentView->reveal(),
            $contentCopier->copy(
                $sourceContentRichEntity->reveal(),
                $sourceDimensionAttributes,
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testCopyFromContentDimensionCollection(): void
    {
        $sourceContentView = $this->prophesize(ContentViewInterface::class);
        $targetContentView = $this->prophesize(ContentViewInterface::class);

        $sourceContentDimensionCollection = $this->prophesize(ContentDimensionCollectionInterface::class);
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentViewFactory = $this->prophesize(ViewFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);

        $contentViewFactory->create($sourceContentDimensionCollection->reveal())
            ->willReturn($sourceContentView->reveal())
            ->shouldBeCalled();

        $contentResolver->resolve($sourceContentView->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentView->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentLoader->reveal(),
            $contentViewFactory->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $this->assertSame(
            $targetContentView->reveal(),
            $contentCopier->copyFromContentDimensionCollection(
                $sourceContentDimensionCollection->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testCopyFromContentView(): void
    {
        $sourceContentView = $this->prophesize(ContentViewInterface::class);
        $targetContentView = $this->prophesize(ContentViewInterface::class);

        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentViewFactory = $this->prophesize(ViewFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);

        $contentResolver->resolve($sourceContentView->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentView->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentLoader->reveal(),
            $contentViewFactory->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $this->assertSame(
            $targetContentView->reveal(),
            $contentCopier->copyFromContentView(
                $sourceContentView->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }
}
