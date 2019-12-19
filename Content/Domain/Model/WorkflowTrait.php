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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

trait WorkflowTrait
{
    /**
     * @var string
     */
    protected $workflowPlace = WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED;

    /**
     * @var \DateTimeImmutable|null
     */
    protected $workflowPublished;

    public function getWorkflowPlace(): string
    {
        return $this->workflowPlace;
    }

    public function setWorkflowPlace(string $workflowPlace): void
    {
        $this->workflowPlace = $workflowPlace;

        if (WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $workflowPlace && !$this->workflowPublished) {
            $this->setWorkflowPublished(new \DateTimeImmutable());
        }
    }

    public function getWorkflowPublished(): ?\DateTimeImmutable
    {
        return $this->workflowPublished;
    }

    public function setWorkflowPublished(?\DateTimeImmutable $workflowPublished): void
    {
        $this->workflowPublished = $workflowPublished;
    }

    public function getWorkflowName(): string
    {
        return WorkflowInterface::WORKFLOW_DEFAULT_NAME;
    }
}
