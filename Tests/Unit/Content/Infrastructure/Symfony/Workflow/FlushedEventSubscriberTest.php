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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Symfony\Workflow;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Symfony\Workflow\FlushedEventSubscriber;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Event\Event;
use Symfony\Component\Workflow\Transition;

class FlushedEventSubscriberTest extends TestCase
{
    protected function createFlushedEventSubscriber(EventDispatcherInterface $eventDispatcher): FlushedEventSubscriber
    {
        return new FlushedEventSubscriber($eventDispatcher);
    }

    public function testOnCompleteAndFlush(): void
    {
        $transition = $this->prophesize(Transition::class);
        $transition->getName()->willReturn('published');

        $event = $this->prophesize(Event::class);
        $event->getWorkflowName()->willReturn(WorkflowInterface::WORKFLOW_DEFAULT_NAME);
        $event->getTransition()->willReturn($transition->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $subscriber = $this->createFlushedEventSubscriber($eventDispatcher->reveal());
        $subscriber->onCompleted($event->reveal());

        $eventName = sprintf('workflow.%s.flushed', WorkflowInterface::WORKFLOW_DEFAULT_NAME);
        $eventDispatcher->dispatch($event->reveal(), $eventName)->shouldBeCalledOnce();
        $eventDispatcher->dispatch($event->reveal(), $eventName . '.published')->shouldBeCalledOnce();

        $subscriber->postFlush();
    }

    public function testOnCompleteAndClear(): void
    {
        $transition = $this->prophesize(Transition::class);
        $transition->getName()->willReturn('published');

        $event = $this->prophesize(Event::class);
        $event->getWorkflowName()->willReturn(WorkflowInterface::WORKFLOW_DEFAULT_NAME);
        $event->getTransition()->willReturn($transition->reveal());

        $eventDispatcher = $this->prophesize(EventDispatcherInterface::class);
        $eventDispatcher->dispatch(Argument::any())->shouldNotBeCalled();

        $subscriber = $this->createFlushedEventSubscriber($eventDispatcher->reveal());
        $subscriber->onCompleted($event->reveal());

        $subscriber->onClear();
    }
}
