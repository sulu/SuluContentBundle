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
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\WorkflowMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(WorkflowInterface::class);
        $contentView->getWorkflowPlace()->shouldNotBeCalled();
        $contentView->getWorkflowPublished()->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementWorkflowInterface(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getWorkflowPlace()->shouldNotBeCalled();
        $dimensionContent->getWorkflowPublished()->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $published = new \DateTimeImmutable();
        $dimensionContent->getWorkflowPlace()->willReturn('draft')->shouldBeCalled();
        $dimensionContent->getWorkflowPublished()->willReturn($published)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(WorkflowInterface::class);
        $contentView->setWorkflowPlace('draft')->shouldBeCalled();
        $contentView->setWorkflowPublished($published)->shouldBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }
}
