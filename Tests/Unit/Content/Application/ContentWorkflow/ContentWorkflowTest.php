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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentWorkflow;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflow;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentInvalidTransitionException;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotExistTransitionException;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentWorkflowTest extends TestCase
{
    protected function createContentWorkflowInstance(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ContentProjectionFactoryInterface $viewFactory
    ): ContentWorkflowInterface {
        return new ContentWorkflow(
            $dimensionRepository,
            $dimensionContentRepository,
            $viewFactory
        );
    }

    public function testTransitionNoWorkflowInterface(): void
    {
        $this->expectException(\RuntimeException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $viewFactory = $this->prophesize(ContentProjectionFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];
        $transitionName = 'request_for_review';

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $this->expectExceptionMessage(sprintf(
            'Expected "%s" but "%s" given.',
            WorkflowInterface::class,
            \get_class($dimensionContent2->reveal()))
        );

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        );
    }

    public function testTransitionNoLocalizedDimensionContent(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $viewFactory = $this->prophesize(ContentProjectionFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];
        $transitionName = 'request_for_review';

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
        ], $dimensionCollection);

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        );
    }

    public function testTransitionNoDimensions(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $viewFactory = $this->prophesize(ContentProjectionFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];
        $transitionName = 'request_for_review';

        $dimensionCollection = new DimensionCollection($dimensionAttributes, []);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            $transitionName
        );
    }

    public function testNotExistTransition(): void
    {
        $this->expectException(ContentNotExistTransitionException::class);
        $this->expectExceptionMessage(
            'Transition "not-exist-transition" is not defined for workflow "content_workflow".'
        );

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $viewFactory = $this->prophesize(ContentProjectionFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->willImplement(WorkflowInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->willImplement(WorkflowInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionContent2->getWorkflowPlace()
            ->willReturn('unpublished')
            ->shouldBeCalled();

        $dimensionContent2->getWorkflowName()
            ->willReturn('content_workflow')
            ->shouldBeCalled();

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $contentWorkflow->apply(
            $contentRichEntity->reveal(),
            $dimensionAttributes,
            'not-exist-transition'
        );
    }

    /**
     * @dataProvider transitionProvider
     */
    public function testTransitions(
        string $currentPlace,
        string $transitionName,
        bool $isTransitionAllowed
    ): void {
        if (!$isTransitionAllowed) {
            $this->expectException(ContentInvalidTransitionException::class);
        }

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $viewFactory = $this->prophesize(ContentProjectionFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->willImplement(WorkflowInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->willImplement(WorkflowInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionContent2->getWorkflowPlace()
            ->willReturn($currentPlace)
            ->shouldBeCalled();

        $dimensionContent2->getWorkflowName()
            ->willReturn('content_workflow')
            ->shouldBeCalled();

        if ($isTransitionAllowed) {
            $dimensionContent2->setWorkflowPlace(Argument::any(), Argument::any())
                ->shouldBeCalled();
        }

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $viewFactory->create($dimensionContentCollection)
            ->willReturn($contentProjection)
            ->shouldBeCalledTimes($isTransitionAllowed ? 1 : 0);

        $this->assertSame(
            $isTransitionAllowed ? $contentProjection->reveal() : null,
            $contentWorkflow->apply(
                $contentRichEntity->reveal(),
                $dimensionAttributes,
                $transitionName
            )
        );
    }

    public function transitionProvider(): \Generator
    {
        $places = [
            'unpublished' => [
                'request_for_review' => true,
                'reject' => false,
                'publish' => true,
                'unpublish' => false,
                'create_draft' => false,
                'remove_draft' => false,
                'request_for_review_draft' => false,
                'reject_draft' => false,
            ],
            'review' => [
                'request_for_review' => false,
                'reject' => true,
                'publish' => true,
                'unpublish' => false,
                'create_draft' => false,
                'remove_draft' => false,
                'request_for_review_draft' => false,
                'reject_draft' => false,
            ],
            'published' => [
                'request_for_review' => false,
                'reject' => false,
                'publish' => true,
                'unpublish' => true,
                'create_draft' => true,
                'remove_draft' => false,
                'request_for_review_draft' => false,
                'reject_draft' => false,
            ],
            'draft' => [
                'request_for_review' => false,
                'reject' => false,
                'publish' => true,
                'unpublish' => true,
                'create_draft' => false,
                'remove_draft' => true,
                'request_for_review_draft' => true,
                'reject_draft' => false,
            ],
            'review_draft' => [
                'request_for_review' => false,
                'reject' => false,
                'publish' => true,
                'unpublish' => false,
                'create_draft' => false,
                'remove_draft' => false,
                'request_for_review_draft' => false,
                'reject_draft' => true,
            ],
        ];

        foreach ($places as $place => $transitions) {
            foreach ($transitions as $transition => $allowed) {
                yield [
                    $place,
                    $transition,
                    $allowed,
                ];
            }
        }
    }
}
