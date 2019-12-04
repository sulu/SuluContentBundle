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
    protected $workflowStage = WorkflowInterface::WORKFLOW_STAGE_UNPUBLISHED;

    public function getWorkflowStage(): string
    {
        return $this->workflowStage;
    }

    public function setWorkflowStage(string $workflowStage): void
    {
        $this->workflowStage = $workflowStage;
    }
}
