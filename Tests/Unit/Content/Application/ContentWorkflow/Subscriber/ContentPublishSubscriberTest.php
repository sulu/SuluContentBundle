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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentWorkflow\Subscriber;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber\ContentPublishSubscriber;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

class ContentPublishSubscriberTest extends TestCase
{
    public function createContentPublisherSubscriberInstance(
        ContentCopierInterface $contentCopier
    ): ContentPublishSubscriber {
        return new ContentPublishSubscriber($contentCopier);
    }

    public function testGetSubscribedEvents(): void
    {
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $this->assertSame([
            'workflow.content_workflow.transition.publish' => 'onPublish',
        ], $contentPublishSubscriber::getSubscribedEvents());
    }

    public function testOnPublishNoContentDimensionInterface(): void
    {
        $contentDimension = $this->prophesize(WorkflowInterface::class);
        $event = new TransitionEvent(
            $contentDimension->reveal(),
            new Marking()
        );

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromContentDimensionCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoContentDimensionCollection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "contentDimensionCollection" given.');

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $contentDimension->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $content->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromContentDimensionCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoContentRichEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "contentRichEntity" given.');

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimensionCollection = $this->prophesize(ContentDimensionCollectionInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $contentDimension->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentDimensionCollection' => $contentDimensionCollection->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromContentDimensionCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoDimensionAttributes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "dimensionAttributes" given.');

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimensionCollection = $this->prophesize(ContentDimensionCollectionInterface::class);
        $content = $this->prophesize(ContentInterface::class);

        $event = new TransitionEvent(
            $contentDimension->reveal(),
            new Marking()
        );
        $event->setContext([
            'contentDimensionCollection' => $contentDimensionCollection->reveal(),
            'contentRichEntity' => $content->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromContentDimensionCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublish(): void
    {
        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimensionCollection = $this->prophesize(ContentDimensionCollectionInterface::class);
        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $contentDimension->reveal(),
            new Marking()
        );
        $event->setContext([
            'contentDimensionCollection' => $contentDimensionCollection->reveal(),
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $content->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $sourceDimensionAttributes = $dimensionAttributes;
        $sourceDimensionAttributes['stage'] = 'live';
        $copiedContentView = $this->prophesize(ContentViewInterface::class);
        $contentCopier->copyFromContentDimensionCollection(
            $contentDimensionCollection->reveal(),
            $content->reveal(),
            $sourceDimensionAttributes
        )
            ->willReturn($copiedContentView->reveal())
            ->shouldBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }
}
