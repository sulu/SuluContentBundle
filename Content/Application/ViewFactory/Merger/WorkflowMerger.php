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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowMerger implements MergerInterface
{
    public function merge(object $contentView, object $dimensionContent): void
    {
        if (!$contentView instanceof WorkflowInterface) {
            return;
        }

        if (!$dimensionContent instanceof WorkflowInterface) {
            return;
        }

        if ($workflowPlace = $dimensionContent->getWorkflowPlace()) {
            $contentView->setWorkflowPlace($workflowPlace);
        }

        if ($workflowPublished = $dimensionContent->getWorkflowPublished()) {
            $contentView->setWorkflowPublished($workflowPublished);
        }
    }
}
