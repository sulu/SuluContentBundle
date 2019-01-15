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

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

interface SeoDimensionRepositoryInterface
{
    public function createDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): SeoDimensionInterface;

    public function findOrCreateDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): SeoDimensionInterface;

    public function findDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ?SeoDimensionInterface;

    public function removeDimension(SeoDimensionInterface $contentDimension): void;

    /**
     * @return SeoDimensionInterface[]
     */
    public function findByResource(string $resourceKey, string $resourceId): array;

    /**
     * @param DimensionIdentifierInterface[] $dimensionIdentifiers
     *
     * @return SeoDimensionInterface[]
     */
    public function findByDimensionIdentifiers(string $resourceKey, string $resourceId, array $dimensionIdentifiers): array;
}
