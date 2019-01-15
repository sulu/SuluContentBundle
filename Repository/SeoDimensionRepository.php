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

namespace Sulu\Bundle\ContentBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimension;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class SeoDimensionRepository extends ServiceEntityRepository implements SeoDimensionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SeoDimension::class);
    }

    public function createDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): SeoDimensionInterface {
        $className = $this->getClassName();
        $seoDimension = new $className($dimensionIdentifier, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($seoDimension);

        return $seoDimension;
    }

    public function findOrCreateDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): SeoDimensionInterface {
        /** @var SeoDimensionInterface|null $seoDimension */
        $seoDimension = $this->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
        if ($seoDimension) {
            return $seoDimension;
        }

        return $this->createDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    public function findDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ?SeoDimensionInterface {
        /** @var SeoDimensionInterface|null $seoDimension */
        $seoDimension = $this->find(
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimensionIdentifier' => $dimensionIdentifier]
        );

        return $seoDimension;
    }

    public function removeDimension(SeoDimensionInterface $contentDimension): void
    {
        $this->getEntityManager()->remove($contentDimension);
    }

    public function findByResource(string $resourceKey, string $resourceId): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId]);
    }

    public function findByDimensionIdentifiers(string $resourceKey, string $resourceId, array $dimensionIdentifiers): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimensionIdentifier' => $dimensionIdentifiers]);
    }
}
