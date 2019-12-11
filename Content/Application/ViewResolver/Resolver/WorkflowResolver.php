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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowResolver implements ResolverInterface
{
    public function resolve(object $contentView, array $viewData): array
    {
        if (!$contentView instanceof WorkflowInterface) {
            return $viewData;
        }

        $viewData['publishedState'] = WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $viewData['workflowPlace'];
        $viewData['published'] = $viewData['workflowPublished'];
        unset($viewData['workflowPublished']);

        return $viewData;
    }

    public function getIgnoredAttributes(object $contentView): array
    {
        return [];
    }
}
