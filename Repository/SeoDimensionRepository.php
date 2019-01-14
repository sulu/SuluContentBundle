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
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
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

    public function create(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoDimensionInterface {
        $className = $this->getClassName();
        $seoDimension = new $className($dimension, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($seoDimension);

        return $seoDimension;
    }

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoDimensionInterface {
        /** @var SeoDimensionInterface|null $seoDimension */
        $seoDimension = $this->findByResource($resourceKey, $resourceId, $dimension);
        if ($seoDimension) {
            return $seoDimension;
        }

        return $this->create($resourceKey, $resourceId, $dimension);
    }

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): ?SeoDimensionInterface {
        /** @var SeoDimensionInterface|null $seoDimension */
        $seoDimension = $this->find(
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]
        );

        return $seoDimension;
    }

    public function findByDimensions(string $resourceKey, string $resourceId, array $dimensions): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimensions]);
    }
}
