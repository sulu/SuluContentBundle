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

/**
 * @implements Traversable<DimensionCollectionInterface>
 */
interface DimensionCollectionInterface extends \Traversable, \Countable
{
    /**
     * @return array<string, string|int|float|bool,null>|null
     */
    public function getLocalizedAttributes(): ?array;

    /**
     * @return array<string, string|int|float|bool,null>
     */
    public function getUnlocalizedAttributes(): array;

    /**
     * @return array<string, string|int|float|bool,null>
     */
    public function getAttributes(): array;

    public function getLocalizedDimension(): ?DimensionInterface;

    public function getUnlocalizedDimension(): ?DimensionInterface;

    /**
     * @return int[]
     */
    public function getDimensionIds(): array;
}
