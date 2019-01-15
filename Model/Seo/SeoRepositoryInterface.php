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

namespace Sulu\Bundle\ContentBundle\Model\Seo;

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

interface SeoRepositoryInterface
{
    public function create(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoInterface;

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoInterface;

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): ?SeoInterface;

    /**
     * @param DimensionInterface[] $dimensions
     *
     * @return SeoInterface[]
     */
    public function findByDimensions(string $resourceKey, string $resourceId, array $dimensions): array;
}
