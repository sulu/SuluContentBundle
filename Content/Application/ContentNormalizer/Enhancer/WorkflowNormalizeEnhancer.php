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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Enhancer;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowNormalizeEnhancer implements NormalizeEnhancerInterface
{
    public function enhance(object $object, array $normalizeData): array
    {
        if (!$object instanceof WorkflowInterface) {
            return $normalizeData;
        }

        $normalizeData['publishedState'] = WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $normalizeData['workflowPlace'];
        $normalizeData['published'] = $normalizeData['workflowPublished'];
        unset($normalizeData['workflowPublished']);

        return $normalizeData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof WorkflowInterface) {
            return [];
        }

        return [];
    }
}
