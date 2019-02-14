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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt;

use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class IconReference implements IconReferenceInterface
{
    /**
     * @var int|null
     */
    private $no;

    /**
     * @var ExcerptDimensionInterface
     */
    private $excerptDimension;

    /**
     * @var MediaInterface
     */
    private $media;

    /**
     * @var int
     */
    private $order;

    public function __construct(
        ExcerptDimensionInterface $excerptDimension,
        MediaInterface $media,
        int $order = 0
    ) {
        $this->excerptDimension = $excerptDimension;
        $this->media = $media;
        $this->order = $order;
    }

    public function __clone()
    {
        $this->no = null;
    }

    public function createClone(ExcerptDimensionInterface $excerptDimension): IconReferenceInterface
    {
        $new = clone $this;
        $new->excerptDimension = $excerptDimension;

        return $new;
    }

    public function getExcerptDimension(): ExcerptDimensionInterface
    {
        return $this->excerptDimension;
    }

    public function getMedia(): MediaInterface
    {
        return $this->media;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): IconReferenceInterface
    {
        $this->order = $order;

        return $this;
    }
}
