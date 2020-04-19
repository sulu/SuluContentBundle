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
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\ItemInterface;
use Sulu\Component\SmartContent\PublishInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class ContentDataItem extends ArrayAccessItem implements ItemInterface, PublishInterface
{
    use ContentDataItemTrait;

    /**
     * @param mixed[] $data
     */
    public function __construct(ContentProjectionInterface $contentProjection, array $data)
    {
        parent::__construct($contentProjection->getContentId(), $data, $contentProjection);
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getTitle(): ?string
    {
        if ($this->exists('title')) {
            return $this->get('title');
        }

        if ($this->exists('name')) {
            return $this->get('name');
        }

        return null;
    }

    protected function getContentProjection(): ContentProjectionInterface
    {
        /** @var ContentProjectionInterface $thingProjection */
        $thingProjection = $this->getResource();

        return $thingProjection;
    }
}
