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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Exception;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class ContentInvalidWorkflowException extends \Exception
{
    /**
     * @param string[] $enabledTransitions
     */
    public function __construct(WorkflowInterface $workflowEntity, string $transitionName, array $enabledTransitions)
    {
        parent::__construct(sprintf(
            'Can not transform from "%s" with "%s" allowed: "%s".',
            $workflowEntity->getWorkflowPlace(),
            $transitionName,
            implode('", "', $enabledTransitions)
        ));
    }
}
