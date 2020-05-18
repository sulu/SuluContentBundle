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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\ItemInterface;
use Sulu\Component\SmartContent\PublishInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class ContentDataItem extends ArrayAccessItem implements ItemInterface, PublishInterface
{
    /**
     * @param mixed[] $data
     */
    public function __construct(DimensionContentInterface $dimensionContent, array $data)
    {
        parent::__construct(
            $dimensionContent->getContentRichEntity()->getId(),
            $data,
            $dimensionContent
        );
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getTitle(): ?string
    {
        if ($this->exists('title') && ($title = $this->get('title'))) {
            return $title;
        }

        if ($this->exists('name') && ($name = $this->get('name'))) {
            return $name;
        }

        return null;
    }

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
        $dimensionContent = $this->getDimensionContent();
        $dimension = $dimensionContent->getDimension();

        if (null === $dimension->getLocale()) {
            return null;
        }

        if (!$dimensionContent instanceof WorkflowInterface) {
            return null;
        }

        return $dimensionContent->getWorkflowPublished();
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getPublishedState(): bool
    {
        $dimensionContent = $this->getDimensionContent();
        $dimension = $dimensionContent->getDimension();

        if (null === $dimension->getLocale()) {
            return false;
        }

        if (DimensionInterface::STAGE_LIVE === $dimension->getStage()) {
            return true;
        }

        if (!$dimensionContent instanceof WorkflowInterface) {
            return true;
        }

        return WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $dimensionContent->getWorkflowPlace();
    }

    protected function getDimensionContent(): DimensionContentInterface
    {
        /** @var DimensionContentInterface $dimensionContent */
        $dimensionContent = $this->getResource();

        return $dimensionContent;
    }
}
