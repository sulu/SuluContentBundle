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

use Doctrine\Common\Collections\ArrayCollection;

class ContentDimensionCollection implements \IteratorAggregate, ContentDimensionCollectionInterface
{
    /**
     * @var ContentDimensionInterface[]
     */
    private $contentDimensions;

    /**
     * @var ContentDimensionInterface
     */
    private $unlocalizedContentDimension;

    /**
     * @var ContentDimensionInterface
     */
    private $localizedContentDimension;

    /**
     * ContentDimensionCollection constructor.
     *
     * @param ContentDimensionInterface[] $contentDimensions
     */
    public function __construct(array $contentDimensions, DimensionCollectionInterface $dimensionCollection)
    {
        $contentDimensionArrayCollection = new ArrayCollection($contentDimensions);

        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();
        if ($unlocalizedDimension) {
            $this->unlocalizedContentDimension = $contentDimensionArrayCollection->filter(
                function (ContentDimensionInterface $contentDimension) use ($unlocalizedDimension) {
                    return $contentDimension->getDimension()->getId() === $unlocalizedDimension->getId();
                }
            )->first();
        }

        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        if ($localizedDimension) {
            $this->localizedContentDimension = $contentDimensionArrayCollection->filter(
                function (ContentDimensionInterface $contentDimension) use ($localizedDimension) {
                    return $contentDimension->getDimension()->getId() === $localizedDimension->getId();
                }
            )->first();
        }

        $this->contentDimensions = array_values($contentDimensions);
    }

    public function getUnlocalizedContentDimension(): ?ContentDimensionInterface
    {
        return $this->unlocalizedContentDimension;
    }

    public function getLocalizedContentDimension(): ?ContentDimensionInterface
    {
        return $this->localizedContentDimension;
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
