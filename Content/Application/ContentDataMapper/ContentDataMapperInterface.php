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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

interface ContentDataMapperInterface
{
    /**
     * @template T of DimensionContentInterface
     *
     * @param array<string, mixed> $data
     * @param DimensionContentCollectionInterface<T> $dimensionContentCollection
     */
    public function map(
        array $data,
        DimensionContentCollectionInterface $dimensionContentCollection
    ): void;
}
