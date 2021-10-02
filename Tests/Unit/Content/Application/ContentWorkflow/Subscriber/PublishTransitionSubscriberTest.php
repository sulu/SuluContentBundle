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
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber\PublishTransitionSubscriber;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

class PublishTransitionSubscriberTest extends TestCase
{
    public function createContentPublisherSubscriberInstance(
        ContentCopierInterface $contentCopier,
        DimensionContentRepositoryInterface $dimensionContentRepository
    ): PublishTransitionSubscriber {
        return new PublishTransitionSubscriber(
            $contentCopier,
            $dimensionContentRepository
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

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
        $contentCopier->copyFromDimensionContentCollection(Argument::cetera())->shouldNotBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

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
            ContentWorkflowInterface::DIMENSION_ATTRIBUTES_CONTEXT_KEY => $dimensionAttributes,
            ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

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
            ContentWorkflowInterface::DIMENSION_ATTRIBUTES_CONTEXT_KEY => $dimensionAttributes,
            ContentWorkflowInterface::DIMENSION_CONTENT_COLLECTION_CONTEXT_KEY => $dimensionContentCollection->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

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
            ContentWorkflowInterface::DIMENSION_CONTENT_COLLECTION_CONTEXT_KEY => $dimensionContentCollection->reveal(),
            ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copyFromDimensionContentCollection(Argument::any(), Argument::any(), Argument::any())
            ->shouldNotBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublish(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $dimensionContent->getWorkflowPublished()->willReturn(null);
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldBeCalled();

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            ContentWorkflowInterface::DIMENSION_CONTENT_COLLECTION_CONTEXT_KEY => $dimensionContentCollection->reveal(),
            ContentWorkflowInterface::DIMENSION_ATTRIBUTES_CONTEXT_KEY => $dimensionAttributes,
            ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $targetDimensionAttributes = $dimensionAttributes;
        $targetDimensionAttributes['stage'] = 'live';

        $resolvedCopiedContent = $this->prophesize(DimensionContentInterface::class);
        $contentCopier->copyFromDimensionContentCollection(
            $dimensionContentCollection->reveal(),
            $contentRichEntity->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($resolvedCopiedContent->reveal())
            ->shouldBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->getLatestVersion($contentRichEntity->reveal())
            ->willReturn(0)
            ->shouldBeCalled();
        $dimensionContentRepository->getLocales($contentRichEntity->reveal(), $targetDimensionAttributes)
            ->willReturn([])
            ->shouldBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

        $contentPublishSubscriber->onPublish($event);
    }

    public function testOnPublishExistingPublished(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $dimensionContent->getWorkflowPublished()->willReturn(new \DateTimeImmutable());
        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            ContentWorkflowInterface::DIMENSION_CONTENT_COLLECTION_CONTEXT_KEY => $dimensionContentCollection->reveal(),
            ContentWorkflowInterface::DIMENSION_ATTRIBUTES_CONTEXT_KEY => $dimensionAttributes,
            ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $targetDimensionAttributes = $dimensionAttributes;
        $targetDimensionAttributes['stage'] = 'live';

        $resolvedCopiedContent = $this->prophesize(DimensionContentInterface::class);
        $contentCopier->copyFromDimensionContentCollection(
            $dimensionContentCollection->reveal(),
            $contentRichEntity->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($resolvedCopiedContent->reveal())
            ->shouldBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->getLatestVersion($contentRichEntity->reveal())
            ->willReturn(0)
            ->shouldBeCalled();
        $dimensionContentRepository->getLocales($contentRichEntity->reveal(), $targetDimensionAttributes)
            ->willReturn(['en', 'de'])
            ->shouldBeCalled();

        $contentCopier->copy(
            $contentRichEntity->reveal(),
            \array_merge($targetDimensionAttributes, ['locale' => 'en']),
            $contentRichEntity->reveal(),
            \array_merge($targetDimensionAttributes, ['locale' => 'en', 'version' => 1])
        )->shouldBeCalled();

        $contentCopier->copy(
            $contentRichEntity->reveal(),
            \array_merge($targetDimensionAttributes, ['locale' => 'de']),
            $contentRichEntity->reveal(),
            \array_merge($targetDimensionAttributes, ['locale' => 'de', 'version' => 1])
        )->shouldBeCalled();

        $contentPublishSubscriber = $this->createContentPublisherSubscriberInstance(
            $contentCopier->reveal(),
            $dimensionContentRepository->reveal()
        );

        $contentPublishSubscriber->onPublish($event);
    }
}
