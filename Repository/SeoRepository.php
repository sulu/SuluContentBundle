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
use Sulu\Bundle\ContentBundle\Model\Seo\Seo;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class SeoRepository extends ServiceEntityRepository implements SeoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seo::class);
    }

    public function create(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoInterface {
        $className = $this->getClassName();
        $seo = new $className($dimension, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($seo);

        return $seo;
    }

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): SeoInterface {
        /** @var SeoInterface|null $seo */
        $seo = $this->findByResource($resourceKey, $resourceId, $dimension);
        if ($seo) {
            return $seo;
        }

        return $this->create($resourceKey, $resourceId, $dimension);
    }

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): ?SeoInterface {
        /** @var SeoInterface|null $seo */
        $seo = $this->find(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]);

        return $seo;
    }

    public function findByDimensions(string $resourceKey, string $resourceId, array $dimensions): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimensions]);
    }
}
