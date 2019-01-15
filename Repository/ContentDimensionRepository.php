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
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimension;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ContentDimensionRepository extends ServiceEntityRepository implements ContentDimensionRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContentDimension::class);
    }

    public function create(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ContentDimensionInterface {
        $className = $this->getClassName();
        $contentDimension = new $className($dimensionIdentifier, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($contentDimension);

        return $contentDimension;
    }

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ContentDimensionInterface {
        /** @var ContentDimensionInterface|null $contentDimension */
        $contentDimension = $this->findByResource($resourceKey, $resourceId, $dimensionIdentifier);
        if ($contentDimension) {
            return $contentDimension;
        }

        return $this->create($resourceKey, $resourceId, $dimensionIdentifier);
    }

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionIdentifierInterface $dimensionIdentifier
    ): ?ContentDimensionInterface {
        /** @var ContentDimensionInterface|null $contentDimension */
        $contentDimension = $this->find(
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimensionIdentifier' => $dimensionIdentifier]
        );

        return $contentDimension;
    }

    public function findByDimensionIdentifiers(string $resourceKey, string $resourceId, array $dimensionIdentifiers): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimensionIdentifier' => $dimensionIdentifiers]);
    }
}
