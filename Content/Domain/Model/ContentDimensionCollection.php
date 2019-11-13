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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

class ContentDimensionCollection implements \IteratorAggregate, ContentDimensionCollectionInterface
{
    /**
     * @var ContentDimensionInterface[]
     */
    private $contentDimensions;

    /**
     * ContentDimensionCollection constructor.
     *
     * @param ContentDimensionInterface[] $contentDimensions
     */
    public function __construct(array $contentDimensions)
    {
        $this->contentDimensions = array_values($contentDimensions);
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->contentDimensions);
    }

    public function count()
    {
        return \count($this->contentDimensions);
    }
}
