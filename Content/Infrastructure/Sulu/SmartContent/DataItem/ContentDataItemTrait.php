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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\DataItem;

use JMS\Serializer\Annotation as Serializer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

trait ContentDataItemTrait
{
    /**
     * @Serializer\VirtualProperty()
     */
    public function getImage(): ?string
    {
        return null;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getPublished(): ?\DateTimeInterface
    {
        $contentProjection = $this->getContentProjection();
        $dimension = $contentProjection->getDimension();

        if (null === $dimension->getLocale()) {
            return null;
        }

        if (!$contentProjection instanceof WorkflowInterface) {
            return null;
        }

        return $contentProjection->getWorkflowPublished();
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getPublishedState(): bool
    {
        $contentProjection = $this->getContentProjection();
        $dimension = $contentProjection->getDimension();

        if (null === $dimension->getLocale()) {
            return false;
        }

        if (DimensionInterface::STAGE_LIVE === $dimension->getStage()) {
            return true;
        }

        if (!$contentProjection instanceof WorkflowInterface) {
            return true;
        }

        return WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $contentProjection->getWorkflowPlace();
    }

    abstract protected function getContentProjection(): ContentProjectionInterface;
}
