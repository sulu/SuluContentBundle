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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Mocks;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

/**
 * Trait for composing a class that wraps a WorkflowInterface mock.
 *
 * @see MockWrapper to learn why this trait is needed.
 *
 * @property mixed $instance
 */
trait WorkflowMockWrapperTrait
{
    public static function getWorkflowName(): string
    {
        return 'mock-workflow-name';
    }

    public function getWorkflowPlace(): ?string
    {
        /** @var WorkflowInterface $instance */
        $instance = $this->instance;

        return $instance->getWorkflowPlace();
    }

    public function setWorkflowPlace(?string $workflowPlace): void
    {
        /** @var WorkflowInterface $instance */
        $instance = $this->instance;

        $instance->setWorkflowPlace($workflowPlace);
    }

    public function getWorkflowPublished(): ?\DateTimeImmutable
    {
        /** @var WorkflowInterface $instance */
        $instance = $this->instance;

        return $instance->getWorkflowPublished();
    }

    public function setWorkflowPublished(?\DateTimeImmutable $workflowPublished): void
    {
        /** @var WorkflowInterface $instance */
        $instance = $this->instance;

        $instance->setWorkflowPublished($workflowPublished);
    }
}
