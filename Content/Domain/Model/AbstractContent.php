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

abstract class AbstractContent implements ContentInterface
{
    /**
     * @var ContentDimensionInterface[]|ArrayCollection
     */
    protected $dimensions;

    /**
     * @return ContentDimensionInterface[]
     */
    public function getDimensions(): iterable
    {
        $this->initializeDimensions();

        return $this->dimensions->toArray();
    }

    public function addDimension(ContentDimensionInterface $contentDimension): void
    {
        $this->initializeDimensions();

        $this->dimensions->add($contentDimension);
    }

    public function removeDimension(ContentDimensionInterface $contentDimension): void
    {
        $this->initializeDimensions();

        $this->dimensions->removeElement($contentDimension);
    }

    private function initializeDimensions(): void
    {
        if (null === $this->dimensions) {
            $this->dimensions = new ArrayCollection();
        }
    }
}
