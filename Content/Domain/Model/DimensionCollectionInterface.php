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

/**
 * @extends \Traversable<DimensionInterface>
 */
interface DimensionCollectionInterface extends \Traversable, \Countable
{
    /**
     * @return mixed[]|null
     */
    public function getLocalizedAttributes(): ?array;

    /**
     * @return mixed[]
     */
    public function getUnlocalizedAttributes(): array;

    /**
     * @return mixed[]
     */
    public function getAttributes(): array;

    public function getLocalizedDimension(): ?DimensionInterface;

    public function getUnlocalizedDimension(): ?DimensionInterface;

    /**
     * @return string[]
     */
    public function getDimensionIds(): array;
}
