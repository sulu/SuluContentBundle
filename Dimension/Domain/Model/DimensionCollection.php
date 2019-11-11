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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

class DimensionCollection implements \IteratorAggregate, DimensionCollectionInterface
{
    /**
     * @var array<string, string|int|float|bool,null>
     */
    private $attributes;

    /**
     * @var array<string, string|int|float|bool,null>|null
     */
    private $localizedAttributes;

    /**
     * @var array<string, string|int|float|bool,null>
     */
    private $unlocalizedAttributes;

    /**
     * @var ArrayCollection<DimensionInterface>
     */
    private $dimensions;

    /**
     * @var DimensionInterface
     */
    private $localizedDimension;

    /**
     * @var DimensionInterface
     */
    private $unlocalizedDimension;

    /**
     * @param array<string, string|int|float|bool,null> $attributes
     * @param DimensionInterface[] $dimensions
     */
    public function __construct(array $attributes, array $dimensions)
    {
        $this->attributes = $attributes;
        $this->dimensions = new ArrayCollection($dimensions);

        if (isset($attributes['locale'])) {
            $this->localizedAttributes = $attributes;
            $attributes['locale'] = null;
            $this->unlocalizedAttributes = $attributes;
        } else {
            $this->unlocalizedAttributes = $attributes;
        }

        $criteria = Criteria::create();
        foreach ($this->unlocalizedAttributes as $key => $value) {
            $criteria->andWhere($criteria->expr()->eq($key, $value));
        }

        $this->unlocalizedDimension = $this->dimensions->matching($criteria)->first() ?: null;

        if ($this->localizedAttributes) {
            $criteria = Criteria::create();
            foreach ($this->localizedAttributes as $key => $value) {
                $criteria->andWhere($criteria->expr()->eq($key, $value));
            }
            $this->localizedDimension = $this->dimensions->matching($criteria)->first() ?: null;
        }
    }

    /**
     * @return array<string, string|int|float|bool,null>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array<string, string|int|float|bool,null>
     */
    public function getUnlocalizedAttributes(): array
    {
        return $this->unlocalizedAttributes;
    }

    /**
     * @return array<string, string|int|float|bool,null>
     */
    public function getLocalizedAttributes(): ?array
    {
        return $this->localizedAttributes;
    }

    public function getLocalizedDimension(): ?DimensionInterface
    {
        return $this->localizedDimension;
    }

    public function getUnlocalizedDimension(): ?DimensionInterface
    {
        return $this->unlocalizedDimension;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->dimensions->toArray());
    }

    public function count(): int
    {
        return \count($this->dimensions);
    }

    public function getDimensionIds(): array
    {
        return array_map(function (DimensionInterface $dimension) {
            return $dimension->getId();
        }, $this->dimensions->toArray());
    }
}
