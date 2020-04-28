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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\SmartContent\DataItem;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\DataItem\ContentDataItem;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleContentProjection;

class ContentDataItemTest extends TestCase
{
    /**
     * @param mixed[] $data
     */
    protected function getContentDataItem(
        ContentProjectionInterface $contentProjection,
        array $data
    ): ContentDataItem {
        return new ContentDataItem($contentProjection, $data);
    }

    public function testGetId()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertSame('123-123', $dataItem->getId());
    }

    public function testGetTitle()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');

        $data = [
            'title' => 'test-title-1',
            'name' => 'test-name-1',
        ];

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), $data);

        $this->assertSame('test-title-1', $dataItem->getTitle());
    }

    public function testGetNameAsTitle()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');

        $data = [
            'title' => null,
            'name' => 'test-name-1',
        ];

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), $data);

        $this->assertSame('test-name-1', $dataItem->getTitle());
    }

    public function testGetImage()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertNull($dataItem->getImage());
    }

    public function testGetPublished()
    {
        $published = new \DateTimeImmutable();

        $contentProjection = $this->prophesize(ExampleContentProjection::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $contentProjection->getDimension()->willReturn($dimension->reveal());
        $contentProjection->getWorkflowPublished()->willReturn($published);

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertSame($published, $dataItem->getPublished());
    }

    public function testGetPublishedLocaleNull()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);
        $contentProjection->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertNull($dataItem->getPublished());
    }

    public function testGetPublishedNoWorkflow()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $contentProjection->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertNull($dataItem->getPublished());
    }

    public function testGetPublishedState()
    {
        $contentProjection = $this->prophesize(ExampleContentProjection::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $contentProjection->getDimension()->willReturn($dimension->reveal());
        $contentProjection->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateLocaleNull()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);
        $contentProjection->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }

    public function testGetPublishedStateStageLive()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);
        $contentProjection->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateNoWorkflow()
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $contentProjection->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateUnpublished()
    {
        $contentProjection = $this->prophesize(ExampleContentProjection::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $contentProjection->getDimension()->willReturn($dimension->reveal());
        $contentProjection->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }

    public function testGetPublishedStateDraft()
    {
        $contentProjection = $this->prophesize(ExampleContentProjection::class);
        $contentProjection->getContentId()->willReturn('123-123');
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $contentProjection->getDimension()->willReturn($dimension->reveal());
        $contentProjection->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_DRAFT);

        $dataItem = $this->getContentDataItem($contentProjection->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }
}
