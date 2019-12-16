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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Helper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowNormalizerHelper implements NormalizerHelperInterface
{
    public function normalize(object $object, array $viewData): array
    {
        if (!$object instanceof WorkflowInterface) {
            return $viewData;
        }

        $viewData['publishedState'] = WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $viewData['workflowPlace'];
        $viewData['published'] = $viewData['workflowPublished'];
        unset($viewData['workflowPublished']);

        return $viewData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        return [];
    }
}
