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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\DataItem\ContentDataItem;

class ContentDataItemTest extends TestCase
{
    /**
     * @param mixed[] $data
     */
    protected function getContentDataItem(
        DimensionContentInterface $resolvedContent,
        array $data
    ): ContentDataItem {
        return new ContentDataItem($resolvedContent, $data);
    }

    public function testGetId(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertSame('123-123', $dataItem->getId());
    }

    public function testGetTitle(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());

        $data = [
            'title' => 'test-title-1',
            'name' => 'test-name-1',
        ];

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), $data);

        $this->assertSame('test-title-1', $dataItem->getTitle());
    }

    public function testGetNameAsTitle(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());

        $data = [
            'title' => null,
            'name' => 'test-name-1',
        ];

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), $data);

        $this->assertSame('test-name-1', $dataItem->getTitle());
    }

    public function testGetImage(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertNull($dataItem->getImage());
    }

    public function testGetPublished(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $published = new \DateTimeImmutable();

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->willImplement(WorkflowInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());
        $resolvedContent->getWorkflowPublished()->willReturn($published);

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        /* @phpstan-ignore-next-line */
        $this->assertSame($published, $dataItem->getPublished());
    }

    public function testGetPublishedLocaleNull(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertNull($dataItem->getPublished());
    }

    public function testGetPublishedNoWorkflow(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertNull($dataItem->getPublished());
    }

    public function testGetPublishedState(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->willImplement(WorkflowInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());
        $resolvedContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateLocaleNull(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }

    public function testGetPublishedStateStageLive(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateNoWorkflow(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertTrue($dataItem->getPublishedState());
    }

    public function testGetPublishedStateUnpublished(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->willImplement(WorkflowInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());
        $resolvedContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }

    public function testGetPublishedStateDraft(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->willImplement(WorkflowInterface::class);
        $resolvedContent->getContentRichEntity()->willReturn($contentRichEntity->reveal());
        $resolvedContent->getDimension()->willReturn($dimension->reveal());
        $resolvedContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_DRAFT);

        $dataItem = $this->getContentDataItem($resolvedContent->reveal(), []);

        $this->assertFalse($dataItem->getPublishedState());
    }
}
