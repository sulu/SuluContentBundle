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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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

    public function testOnPublishNoDimensionContentInterface(): void
    {
        $dimensionContent = $this->prophesize(WorkflowInterface::class);
        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoDimensionContentCollection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "dimensionContentCollection" given.');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoContentRichEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "contentRichEntity" given.');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'dimensionContentCollection' => $dimensionContentCollection->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishNoDimensionAttributes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No "dimensionAttributes" given.');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionContentCollection' => $dimensionContentCollection->reveal(),
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublish(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionContentCollection' => $dimensionContentCollection->reveal(),
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $sourceDimensionAttributes = $dimensionAttributes;
        $sourceDimensionAttributes['stage'] = 'live';
        $copiedContentView = $this->prophesize(ContentViewInterface::class);
        $contentCopier->copyFromDimensionContentCollection(
            $dimensionContentCollection->reveal(),
            $contentRichEntity->reveal(),
            $sourceDimensionAttributes
        )
            ->willReturn($copiedContentView->reveal())
            ->shouldBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance($contentCopier->reveal());

        $contentPublishSubscriber->onPublish($event);
    }
}
