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
use Doctrine\Common\Collections\Criteria;

/**
 * @template-covariant T of DimensionContentInterface
 *
 * @implements \IteratorAggregate<T>
 * @implements DimensionContentCollectionInterface<T>
 */
class DimensionContentCollection implements \IteratorAggregate, DimensionContentCollectionInterface
{
    /**
     * @var ArrayCollection<int, T>
     */
    private $dimensionContents;

    /**
     * @var mixed[]
     */
    private $dimensionAttributes;

    /**
     * @var class-string<T>
     */
    private $dimensionContentClass;

    /**
     * @var mixed[]
     */
    private $defaultDimensionAttributes;

    /**
     * DimensionContentCollection constructor.
     *
     * @param T[] $dimensionContents
     * @param mixed[] $dimensionAttributes
     * @param class-string<T> $dimensionContentClass
     */
    public function __construct(
        array $dimensionContents,
        array $dimensionAttributes,
        string $dimensionContentClass
    ) {
        $this->dimensionContents = new ArrayCollection($dimensionContents);
        $this->dimensionContentClass = $dimensionContentClass;
        $this->defaultDimensionAttributes = $dimensionContentClass::getDefaultDimensionAttributes();

        $this->dimensionAttributes = $dimensionAttributes;
    }

    public function getDimensionContentClass(): string
    {
        return $this->dimensionContentClass;
    }

    public function getDimensionContent(array $dimensionAttributes): ?DimensionContentInterface
    {
        $dimensionAttributes = \array_merge($this->defaultDimensionAttributes, $dimensionAttributes);

        $criteria = Criteria::create();
        foreach ($dimensionAttributes as $key => $value) {
            if (null === $value) {
                $expr = $criteria->expr()->isNull($key);
            } else {
                $expr = $criteria->expr()->eq($key, $value);
            }

            $criteria->andWhere($expr);
        }

        return $this->dimensionContents->matching($criteria)->first() ?: null;
    }

    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }

    #[\ReturnTypeWillChange]
    public function getIterator()
    {
        return $this->dimensionContents;
    }

    public function count(): int
    {
        return \count($this->dimensionContents);
    }
}
