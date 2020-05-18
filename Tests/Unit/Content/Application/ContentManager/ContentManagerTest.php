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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentManager;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManager;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentManagerTest extends TestCase
{
    protected function createContentManagerInstance(
        ContentResolverInterface $contentResolver,
        ContentPersisterInterface $contentPersister,
        ContentNormalizerInterface $contentNormalizer,
        ContentCopierInterface $contentCopier,
        ContentWorkflowInterface $contentWorkflow
    ): ContentManagerInterface {
        return new ContentManager($contentResolver, $contentPersister, $contentNormalizer, $contentCopier, $contentWorkflow);
    }

    public function testResolve(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($dimensionContent->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $dimensionContent->reveal(),
            $contentManager->resolve($contentRichEntity->reveal(), $dimensionAttributes)
        );
    }

    public function testPersist(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $data = ['data' => 'value'];
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentPersister->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
            ->willReturn($dimensionContent->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $dimensionContent->reveal(),
            $contentManager->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
        );
    }

    public function testNormalize(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentNormalizer->normalize($dimensionContent->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $this->assertSame(
            ['resolved' => 'data'],
            $contentManager->normalize($dimensionContent->reveal())
        );
    }

    public function testCopy(): void
    {
        $copiedContent = $this->prophesize(DimensionContentInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentCopier->copy(
            $sourceContentRichEntity->reveal(),
            $sourceDimensionAttributes,
            $targetContentRichEntity->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($copiedContent->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $copiedContent->reveal(),
            $contentManager->copy(
                $sourceContentRichEntity->reveal(),
                $sourceDimensionAttributes,
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testApplyTransition(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en'];
        $transitionName = 'review';

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentNormalizer = $this->prophesize(ContentNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentManager = $this->createContentManagerInstance(
            $contentResolver->reveal(),
            $contentPersister->reveal(),
            $contentNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        )
            ->willReturn($dimensionContent->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $dimensionContent->reveal(),
            $contentManager->applyTransition(
                $contentRichEntity->reveal(),
                $dimensionAttributes,
                $transitionName
            )
        );
    }
}
