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
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\ContentProjectionNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

class ContentCopierTest extends TestCase
{
    protected function createContentCopierInstance(
        ContentResolverInterface $contentResolver,
        ContentProjectionFactoryInterface $viewFactory,
        ContentPersisterInterface $contentPersister,
        ContentProjectionNormalizerInterface $contentProjectionNormalizer
    ): ContentCopierInterface {
        return new ContentCopier(
            $contentResolver,
            $viewFactory,
            $contentPersister,
            $contentProjectionNormalizer
        );
    }

    public function testCopy(): void
    {
        $sourceContentProjection = $this->prophesize(ContentProjectionInterface::class);
        $targetContentProjection = $this->prophesize(ContentProjectionInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentProjectionFactory = $this->prophesize(ContentProjectionFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);

        $contentResolver->resolve($sourceContentRichEntity->reveal(), $sourceDimensionAttributes)
            ->willReturn($sourceContentProjection->reveal())
            ->shouldBeCalled();

        $contentProjectionNormalizer->normalize($sourceContentProjection->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentProjection->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentProjectionFactory->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal()
        );

        $this->assertSame(
            $targetContentProjection->reveal(),
            $contentCopier->copy(
                $sourceContentRichEntity->reveal(),
                $sourceDimensionAttributes,
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testCopyFromDimensionContentCollection(): void
    {
        $sourceContentProjection = $this->prophesize(ContentProjectionInterface::class);
        $targetContentProjection = $this->prophesize(ContentProjectionInterface::class);

        $sourceContentDimensionCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentProjectionFactory = $this->prophesize(ContentProjectionFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);

        $contentProjectionFactory->create($sourceContentDimensionCollection->reveal())
            ->willReturn($sourceContentProjection->reveal())
            ->shouldBeCalled();

        $contentProjectionNormalizer->normalize($sourceContentProjection->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentProjection->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentProjectionFactory->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal()
        );

        $this->assertSame(
            $targetContentProjection->reveal(),
            $contentCopier->copyFromDimensionContentCollection(
                $sourceContentDimensionCollection->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testCopyFromContentProjection(): void
    {
        $sourceContentProjection = $this->prophesize(ContentProjectionInterface::class);
        $targetContentProjection = $this->prophesize(ContentProjectionInterface::class);

        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentProjectionFactory = $this->prophesize(ContentProjectionFactoryInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);

        $contentProjectionNormalizer->normalize($sourceContentProjection->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($targetContentProjection->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentProjectionFactory->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal()
        );

        $this->assertSame(
            $targetContentProjection->reveal(),
            $contentCopier->copyFromContentProjection(
                $sourceContentProjection->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }
}
