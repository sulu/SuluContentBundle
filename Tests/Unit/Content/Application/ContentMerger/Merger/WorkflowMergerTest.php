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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentMerger\Merger;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\WorkflowMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
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

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(WorkflowInterface::class);
        $contentProjection->getWorkflowPlace()->shouldNotBeCalled();
        $contentProjection->getWorkflowPublished()->shouldNotBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementWorkflowInterface(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getWorkflowPlace()->shouldNotBeCalled();
        $dimensionContent->getWorkflowPublished()->shouldNotBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getWorkflowMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $published = new \DateTimeImmutable();
        $dimensionContent->getWorkflowPlace()->willReturn('draft')->shouldBeCalled();
        $dimensionContent->getWorkflowPublished()->willReturn($published)->shouldBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(WorkflowInterface::class);
        $contentProjection->setWorkflowPlace('draft')->shouldBeCalled();
        $contentProjection->setWorkflowPublished($published)->shouldBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }
}
