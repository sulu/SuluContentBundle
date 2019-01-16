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
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimension;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ExcerptDimensionRepository extends ServiceEntityRepository implements ExcerptDimensionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExcerptDimension::class);
    }

    public function createDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ExcerptDimensionInterface {
        $className = $this->getClassName();
        $excerptDimension = new $className($dimensionIdentifier, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($excerptDimension);

        return $excerptDimension;
    }

    public function findOrCreateDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ExcerptDimensionInterface {
        /** @var ExcerptDimensionInterface|null $excerptDimension */
        $excerptDimension = $this->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
        if ($excerptDimension) {
            return $excerptDimension;
        }

        return $this->createDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    public function findDimension(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ?ExcerptDimensionInterface {
        /** @var ExcerptDimensionInterface|null $excerptDimension */
        $excerptDimension = $this->findOneBy(
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimensionIdentifier' => $dimensionIdentifier]
        );

        return $excerptDimension;
    }

    public function removeDimension(ExcerptDimensionInterface $contentDimension): void
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
