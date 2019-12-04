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

interface WorkflowInterface
{
    const WORKFLOW_STAGE_UNPUBLISHED = 'unpublished';

    const WORKFLOW_STAGE_REVIEW = 'review';

    const WORKFLOW_STAGE_PUBLISHED = 'published';

    const WORKFLOW_STAGE_DRAFT = 'draft';

    const WORKFLOW_STAGE_REVIEW_DRAFT = 'review_draft';

    public function getWorkflowStage(): string;

    public function setWorkflowStage(string $workflowStage): void;
}
