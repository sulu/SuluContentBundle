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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;

class WorkflowTraitTest extends TestCase
{
    use WorkflowTrait;

    protected function getWorkflowInstance(): WorkflowInterface
    {
        return new class() implements WorkflowInterface {
            use WorkflowTrait;
        };
    }

    public function testGetWorkflowStage(): void
    {
        $workflow = $this->getWorkflowInstance();
        $this->assertSame(WorkflowInterface::WORKFLOW_STAGE_UNPUBLISHED, $workflow->getWorkflowStage());
    }

    public function testSetWorkflowStage(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowStage(WorkflowInterface::WORKFLOW_STAGE_REVIEW);
        $this->assertSame(WorkflowInterface::WORKFLOW_STAGE_REVIEW, $workflow->getWorkflowStage());
    }
}
