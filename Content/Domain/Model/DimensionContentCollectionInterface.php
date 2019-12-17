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
 * @implements Traversable<DimensionContentInterface>
 */
interface DimensionContentCollectionInterface extends \Traversable, \Countable
{
    public function getUnlocalizedDimensionContent(): ?DimensionContentInterface;

    public function getLocalizedDimensionContent(): ?DimensionContentInterface;
}
