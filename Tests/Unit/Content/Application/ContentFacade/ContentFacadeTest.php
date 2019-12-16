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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentFacade;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacade;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacadeInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\ContentProjectionNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

class ContentFacadeTest extends TestCase
{
    protected function createContentFacadeInstance(
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        ContentProjectionNormalizerInterface $contentProjectionNormalizer,
        ContentCopierInterface $contentCopier,
        ContentWorkflowInterface $contentWorkflow
    ): ContentFacadeInterface {
        return new ContentFacade($contentLoader, $contentPersister, $contentProjectionNormalizer, $contentCopier, $contentWorkflow);
    }

    public function testLoad(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentLoader->load($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentFacade->load($contentRichEntity->reveal(), $dimensionAttributes)
        );
    }

    public function testPersist(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $data = ['data' => 'value'];
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentPersister->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentFacade->persist($contentRichEntity->reveal(), $data, $dimensionAttributes)
        );
    }

    public function testResolve(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentProjectionNormalizer->normalize($contentProjection->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $this->assertSame(
            ['resolved' => 'data'],
            $contentFacade->normalize($contentProjection->reveal())
        );
    }

    public function testCopy(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $sourceContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentCopier->copy(
            $sourceContentRichEntity->reveal(),
            $sourceDimensionAttributes,
            $targetContentRichEntity->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentFacade->copy(
                $sourceContentRichEntity->reveal(),
                $sourceDimensionAttributes,
                $targetContentRichEntity->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testTransition(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en'];
        $transitionName = 'review';

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentProjectionNormalizer = $this->prophesize(ContentProjectionNormalizerInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentProjectionNormalizer->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        )
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentProjection->reveal(),
            $contentFacade->applyTransition(
                $contentRichEntity->reveal(),
                $dimensionAttributes,
                $transitionName
            )
        );
    }
}
