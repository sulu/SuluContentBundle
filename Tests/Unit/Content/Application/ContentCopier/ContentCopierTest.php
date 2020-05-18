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
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentCopierTest extends TestCase
{
    protected function createContentCopierInstance(
        ContentResolverInterface $contentResolver,
        ContentMergerInterface $contentMerger,
        ContentPersisterInterface $contentPersister,
        ContentNormalizerInterface $contentNormalizer
    ): ContentCopierInterface {
        return new ContentCopier(
            $contentResolver,
            $contentMerger,
            $contentPersister,
            $contentNormalizer
        );
    }

    public function testCopy(): void
    {
        $resolvedSourceContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedTargetContent = $this->prophesize(DimensionContentInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);

        $contentResolver->resolve($sourceContentRichEntity->reveal(), $sourceDimensionAttributes)
            ->willReturn($resolvedSourceContent->reveal())
            ->shouldBeCalled();

        $contentNormalizer->normalize($resolvedSourceContent->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($resolvedTargetContent->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentMerger->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal()
        );

        $this->assertSame(
            $resolvedTargetContent->reveal(),
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
        $resolvedSourceContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedTargetContent = $this->prophesize(DimensionContentInterface::class);

        $sourceContentDimensionCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);

        $contentMerger->mergeCollection($sourceContentDimensionCollection->reveal())
            ->willReturn($resolvedSourceContent->reveal())
            ->shouldBeCalled();

        $contentNormalizer->normalize($resolvedSourceContent->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($resolvedTargetContent->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentMerger->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal()
        );

        $this->assertSame(
            $resolvedTargetContent->reveal(),
            $contentCopier->copyFromDimensionContentCollection(
                $sourceContentDimensionCollection->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testCopyFromContentProjection(): void
    {
        $resolvedSourceContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedTargetContent = $this->prophesize(DimensionContentInterface::class);

        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);

        $contentNormalizer->normalize($resolvedSourceContent->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $contentPersister->persist($targetContentRichEntity, ['resolved' => 'data'], $targetDimensionAttributes)
            ->willReturn($resolvedTargetContent->reveal())
            ->shouldBeCalled();

        $contentCopier = $this->createContentCopierInstance(
            $contentResolver->reveal(),
            $contentMerger->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal()
        );

        $this->assertSame(
            $resolvedTargetContent->reveal(),
            $contentCopier->copyFromContentProjection(
                $resolvedSourceContent->reveal(),
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }
}
