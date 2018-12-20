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
use Sulu\Bundle\ContentBundle\Model\Content\Content;
use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ContentRepository extends ServiceEntityRepository implements ContentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Content::class);
    }

    public function findOrCreate(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): ContentInterface {
        /** @var ContentInterface|null $content */
        $content = $this->findByResource($resourceKey, $resourceId, $dimension);
        if ($content) {
            return $content;
        }

        $className = $this->getClassName();
        $content = new $className($dimension, $resourceKey, $resourceId);

        $this->getEntityManager()->persist($content);

        return $content;
    }

    public function findByResource(
        string $resourceKey,
        string $resourceId,
        DimensionInterface $dimension
    ): ?ContentInterface {
        /** @var ContentInterface|null $content */
        $content = $this->find(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]);

        return $content;
    }

    public function findByDimensions(string $resourceKey, string $resourceId, array $dimensions): array
    {
        return $this->findBy(['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimensions]);
    }
}
