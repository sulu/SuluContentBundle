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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory\Merger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\WorkflowMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowMergerTest extends TestCase
{
    protected function getWorkflowMergerInstance(): MergerInterface
    {
        return new WorkflowMerger();
    }

    public function testMergeDimensionNotImplementWorkflowInterface(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(WorkflowInterface::class);
        $contentView->setWorkflowStage(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeViewNotImplementWorkflowInterface(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(WorkflowInterface::class);
        $contentDimension->getWorkflowStage(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(WorkflowInterface::class);
        $contentDimension->getWorkflowStage()->willReturn('draft')->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(WorkflowInterface::class);
        $contentView->setWorkflowStage('draft')->shouldBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }
}
