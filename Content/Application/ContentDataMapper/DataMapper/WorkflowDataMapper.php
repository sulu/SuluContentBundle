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
        if (!$unlocalizedObject instanceof WorkflowInterface) {
            return;
        }

        if ($localizedObject) {
            if (!$localizedObject instanceof WorkflowInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedObject" from type "%s" but "%s" given.', WorkflowInterface::class, \get_class($localizedObject)));
            }

            $this->setWorkflowData($localizedObject, $data);

            return;
        }

        $this->setWorkflowData($unlocalizedObject, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setWorkflowData(WorkflowInterface $object, array $data): void
    {
        if (!$object instanceof DimensionContentInterface
            || DimensionInterface::STAGE_LIVE !== $object->getDimension()->getStage()) {
            return;
        }

        $published = $data['published'] ?? null;

        if (!$published) {
            return;
        }

        $object->setWorkflowPublished(new \DateTimeImmutable($published));
    }
}
