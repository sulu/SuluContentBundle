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

class DimensionContentCollection implements \IteratorAggregate, DimensionContentCollectionInterface
{
    /**
     * @var DimensionContentInterface[]
     */
    private $dimensionContents;

    /**
     * @var DimensionContentInterface
     */
    private $unlocalizedDimensionContent;

    /**
     * @var DimensionContentInterface
     */
    private $localizedDimensionContent;

    /**
     * DimensionContentCollection constructor.
     *
     * @param DimensionContentInterface[] $dimensionContents
     */
    public function __construct(array $dimensionContents, DimensionCollectionInterface $dimensionCollection)
    {
        $dimensionContentArrayCollection = new ArrayCollection($dimensionContents);

        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();
        if ($unlocalizedDimension) {
            $this->unlocalizedDimensionContent = $dimensionContentArrayCollection->filter(
                function (DimensionContentInterface $dimensionContent) use ($unlocalizedDimension) {
                    return $dimensionContent->getDimension()->getId() === $unlocalizedDimension->getId();
                }
            )->first() ?: null;
        }

        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        if ($localizedDimension) {
            $this->localizedDimensionContent = $dimensionContentArrayCollection->filter(
                function (DimensionContentInterface $dimensionContent) use ($localizedDimension) {
                    return $dimensionContent->getDimension()->getId() === $localizedDimension->getId();
                }
            )->first() ?: null;
        }

        $this->dimensionContents = array_values($dimensionContents);
    }

    public function getUnlocalizedDimensionContent(): ?DimensionContentInterface
    {
        return $this->unlocalizedDimensionContent;
    }

    public function getLocalizedDimensionContent(): ?DimensionContentInterface
    {
        return $this->localizedDimensionContent;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->dimensionContents);
    }

    public function count()
    {
        return \count($this->dimensionContents);
    }
}
