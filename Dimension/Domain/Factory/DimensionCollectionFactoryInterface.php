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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Factory;

use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollectionInterface;

interface DimensionCollectionFactoryInterface
{
    /**
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    public function create(array $dimensionAttributes): DimensionCollectionInterface;
}
