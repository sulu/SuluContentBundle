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
    public function __construct(DimensionContentInterface $resolvedContent, array $data)
    {
        parent::__construct($resolvedContent->getContentRichEntity()->getId(), $data, $resolvedContent);
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
        $resolvedContent = $this->getResolvedContent();
        $dimension = $resolvedContent->getDimension();

        if (null === $dimension->getLocale()) {
            return null;
        }

        if (!$resolvedContent instanceof WorkflowInterface) {
            return null;
        }

        return $resolvedContent->getWorkflowPublished();
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getPublishedState(): bool
    {
        $resolvedContent = $this->getResolvedContent();
        $dimension = $resolvedContent->getDimension();

        if (null === $dimension->getLocale()) {
            return false;
        }

        if (DimensionInterface::STAGE_LIVE === $dimension->getStage()) {
            return true;
        }

        if (!$resolvedContent instanceof WorkflowInterface) {
            return true;
        }

        return WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $resolvedContent->getWorkflowPlace();
    }

    protected function getResolvedContent(): DimensionContentInterface
    {
        /** @var DimensionContentInterface $resolvedContent */
        $resolvedContent = $this->getResource();

        return $resolvedContent;
    }
}
