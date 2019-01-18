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
     * @var int
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

    public function __construct(
        ExcerptDimensionInterface $excerptDimension,
        TagInterface $tag
    ) {
        $this->excerptDimension = $excerptDimension;
        $this->tag = $tag;
    }

    public function getExcerptDimension(): ExcerptDimensionInterface
    {
        return $this->excerptDimension;
    }

    public function getTag(): TagInterface
    {
        return $this->tag;
    }
}
