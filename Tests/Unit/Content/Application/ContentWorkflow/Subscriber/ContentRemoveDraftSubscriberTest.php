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
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber\ContentRemoveDraftSubscriber;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

class ContentRemoveDraftSubscriberTest extends TestCase
{
    public function createContentRemoveDraftSubscriberInstance(
        ContentCopierInterface $contentCopier
    ): ContentRemoveDraftSubscriber {
        return new ContentRemoveDraftSubscriber($contentCopier);
    }

    public function testGetSubscribedEvents(): void
    {
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentRemoveDraftSubscriber = $this->createContentRemoveDraftSubscriberInstance($contentCopier->reveal());

        $this->assertSame([
            'workflow.content_workflow.transition.remove_draft' => 'onRemoveDraft',
        ], $contentRemoveDraftSubscriber::getSubscribedEvents());
    }

    public function testOnRemoveDraftNoDimensionContentInterface(): void
    {
        $dimensionContent = $this->prophesize(WorkflowInterface::class);
        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copy(Argument::cetera())->shouldNotBeCalled();

        $contentRemoveDraftSubscriber = $this->createContentRemoveDraftSubscriberInstance($contentCopier->reveal());

        $contentRemoveDraftSubscriber->onRemoveDraft($event);
    }

    public function testOnRemoveDraftNoDimensionAttributes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transition context must contain "dimensionAttributes".');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copy(Argument::cetera())->shouldNotBeCalled();

        $contentRemoveDraftSubscriber = $this->createContentRemoveDraftSubscriberInstance($contentCopier->reveal());

        $contentRemoveDraftSubscriber->onRemoveDraft($event);
    }

    public function testOnRemoveDraftNoContentRichEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transition context must contain "contentRichEntity".');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
        ]);

        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentCopier->copy(Argument::cetera())->shouldNotBeCalled();

        $contentRemoveDraftSubscriber = $this->createContentRemoveDraftSubscriberInstance($contentCopier->reveal());

        $contentRemoveDraftSubscriber->onRemoveDraft($event);
    }

    public function testOnRemoveDraft(): void
    {
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
        $contentCopier->copy(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live'],
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'draft']
        )
            ->shouldBeCalled();

        $contentRemoveDraftSubscriber = $this->createContentRemoveDraftSubscriberInstance($contentCopier->reveal());

        $contentRemoveDraftSubscriber->onRemoveDraft($event);
    }
}
