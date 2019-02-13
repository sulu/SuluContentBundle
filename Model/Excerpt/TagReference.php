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

use Sulu\Bundle\TagBundle\Tag\TagInterface;

class TagReference implements TagReferenceInterface
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
     * @var TagInterface
     */
    private $tag;

    /**
     * @var int
     */
    private $order;

    public function __construct(
        ExcerptDimensionInterface $excerptDimension,
        TagInterface $tag,
        int $order = 0
    ) {
        $this->excerptDimension = $excerptDimension;
        $this->tag = $tag;
        $this->order = $order;
    }

    public function __clone()
    {
        $this->no = null;
    }

    public function createClone(ExcerptDimensionInterface $excerptDimension): TagReferenceInterface
    {
        $new = clone $this;
        $new->excerptDimension = $excerptDimension;

        return $new;
    }

    public function getExcerptDimension(): ExcerptDimensionInterface
    {
        return $this->excerptDimension;
    }

    public function getTag(): TagInterface
    {
        return $this->tag;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(int $order): TagReferenceInterface
    {
        $this->order = $order;

        return $this;
    }
}
