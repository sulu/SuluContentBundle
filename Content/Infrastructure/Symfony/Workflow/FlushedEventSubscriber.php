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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Symfony\Workflow;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\Event;

/**
 * @internal
 */
class FlushedEventSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            'workflow.completed' => 'onCompleted',
        ];
    }

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var mixed[]
     */
    private $eventsToDispatch = [];

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onCompleted(Event $event): void
    {
        if (WorkflowInterface::WORKFLOW_DEFAULT_NAME !== $event->getWorkflowName()) {
            return;
        }

        $eventName = sprintf('workflow.%s.flushed', $event->getWorkflowName());

        $this->eventsToDispatch[] = [$event, $eventName];
        $this->eventsToDispatch[] = [$event, sprintf('%s.%s', $eventName, $event->getTransition()->getName())];
    }

    public function postFlush(): void
    {
        foreach ($this->eventsToDispatch as $item) {
            $this->eventDispatcher->dispatch($item[0], $item[1]);
        }

        $this->eventsToDispatch = [];
    }

    public function onClear(): void
    {
        $this->eventsToDispatch = [];
    }
}
