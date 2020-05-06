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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowDataMapper implements DataMapperInterface
{
    public function map(
        array $data,
        object $unlocalizedObject,
        ?object $localizedObject = null
    ): void {
        if (!$localizedObject
            || !$localizedObject instanceof WorkflowInterface
            || !$localizedObject instanceof DimensionContentInterface) {
            return;
        }

        if (DimensionInterface::STAGE_LIVE !== $localizedObject->getDimension()->getStage()) {
            return;
        }

        $published = $data['published'] ?? null;

        if (!$published) {
            return;
        }

        $localizedObject->setWorkflowPublished(new \DateTimeImmutable($published));
    }
}
