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
    public function __construct(DimensionContentInterface $resolvedDimensionContent, array $data)
    {
        parent::__construct(
            $resolvedDimensionContent->getContentRichEntity()->getId(),
            $data,
            $resolvedDimensionContent
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
        $resolvedDimensionContent = $this->getResolvedDimensionContent();
        $dimension = $resolvedDimensionContent->getDimension();

        if (null === $dimension->getLocale()) {
            return null;
        }

        if (!$resolvedDimensionContent instanceof WorkflowInterface) {
            return null;
        }

        return $resolvedDimensionContent->getWorkflowPublished();
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getPublishedState(): bool
    {
        $resolvedDimensionContent = $this->getResolvedDimensionContent();
        $dimension = $resolvedDimensionContent->getDimension();

        if (null === $dimension->getLocale()) {
            return false;
        }

        if (DimensionInterface::STAGE_LIVE === $dimension->getStage()) {
            return true;
        }

        if (!$resolvedDimensionContent instanceof WorkflowInterface) {
            return true;
        }

        return WorkflowInterface::WORKFLOW_PLACE_PUBLISHED === $resolvedDimensionContent->getWorkflowPlace();
    }

    protected function getResolvedDimensionContent(): DimensionContentInterface
    {
        /** @var DimensionContentInterface $resolvedDimensionContent */
        $resolvedDimensionContent = $this->getResource();

        return $resolvedDimensionContent;
    }
}
