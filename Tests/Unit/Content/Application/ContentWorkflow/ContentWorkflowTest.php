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
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentInvalidWorkflowException;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentWorkflowTest extends TestCase
{
    protected function createContentWorkflowInstance(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory
    ): ContentWorkflowInterface {
        return new ContentWorkflow(
            $dimensionRepository,
            $contentDimensionRepository,
            $viewFactory
        );
    }

    public function testTransitionNoWorkflowInterface(): void
    {
        $this->expectException(\RuntimeException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $contentDimensionRepository->reveal(),
            $viewFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];
        $transitionName = 'request_for_review';

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $this->expectExceptionMessage(sprintf(
            'Expected "%s" but "%s" given.',
            WorkflowInterface::class,
            \get_class($contentDimension2->reveal()))
        );

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $contentDimensionRepository->load($content->reveal(), $dimensionCollection)
            ->willReturn($contentDimensionCollection)
            ->shouldBeCalled();

        $contentWorkflow->transition(
            $content->reveal(),
            $dimensionAttributes,
            $transitionName
        );
    }

    public function testTransitionNoLocalizedContentDimension(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $contentDimensionRepository->reveal(),
            $viewFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];
        $transitionName = 'request_for_review';

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
        ], $dimensionCollection);

        $contentDimensionRepository->load($content->reveal(), $dimensionCollection)
            ->willReturn($contentDimensionCollection)
            ->shouldBeCalled();

        $contentWorkflow->transition(
            $content->reveal(),
            $dimensionAttributes,
            $transitionName
        );
    }

    public function testNotExistTransition(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'The transition "%s" does not exist.',
            'not-exist-transition'
        ));

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $contentDimensionRepository->reveal(),
            $viewFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->willImplement(WorkflowInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->willImplement(WorkflowInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $contentDimension2->getWorkflowPlace()
            ->willReturn('unpublished')
            ->shouldBeCalled();

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $contentDimensionRepository->load($content->reveal(), $dimensionCollection)
            ->willReturn($contentDimensionCollection)
            ->shouldBeCalled();

        $contentWorkflow->transition(
            $content->reveal(),
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
            $this->expectException(ContentInvalidWorkflowException::class);
        }

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);

        $contentWorkflow = $this->createContentWorkflowInstance(
            $dimensionRepository->reveal(),
            $contentDimensionRepository->reveal(),
            $viewFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $dimension1 = new Dimension('123-456', []);
        $dimension2 = new Dimension('456-789', $dimensionAttributes);

        $dimensionCollection = new DimensionCollection($dimensionAttributes, [$dimension1, $dimension2]);

        $dimensionRepository->findByAttributes($dimensionAttributes)
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->willImplement(WorkflowInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->willImplement(WorkflowInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $contentDimension2->getWorkflowPlace()
            ->willReturn($currentPlace)
            ->shouldBeCalled();

        if ($isTransitionAllowed) {
            $contentDimension2->setWorkflowPlace(Argument::any(), Argument::any())
                ->shouldBeCalled();
        }

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $contentDimensionRepository->load($content->reveal(), $dimensionCollection)
            ->willReturn($contentDimensionCollection)
            ->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $viewFactory->create($contentDimensionCollection)
            ->willReturn($contentView)
            ->shouldBeCalledTimes($isTransitionAllowed ? 1 : 0);

        $this->assertSame(
            $isTransitionAllowed ? $contentView->reveal() : null,
            $contentWorkflow->transition(
                $content->reveal(),
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
                'publish' => false,
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
