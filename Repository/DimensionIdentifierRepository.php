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
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Ramsey\Uuid\Uuid;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifier;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierAttribute;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class DimensionIdentifierRepository extends ServiceEntityRepository implements DimensionIdentifierRepositoryInterface
{
    /**
     * @var DimensionIdentifierInterface[]
     */
    private $cachedEntities = [];

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DimensionIdentifier::class);
    }

    public function create(array $attributes = []): DimensionIdentifierInterface
    {
        $key = md5(serialize($attributes));
        if (array_key_exists($key, $this->cachedEntities)) {
            return $this->cachedEntities[$key];
        }

        $attributeEntities = [];
        foreach ($attributes as $type => $value) {
            $attributeEntities[] = new DimensionIdentifierAttribute($type, $value);
        }

        $dimensionIdentifier = new DimensionIdentifier(Uuid::uuid4()->toString(), $attributeEntities);
        $this->getEntityManager()->persist($dimensionIdentifier);

        return $this->cachedEntities[$key] = $dimensionIdentifier;
    }

    public function findOrCreateByAttributes(array $attributes): DimensionIdentifierInterface
    {
        $dimensionIdentifier = $this->findOneByAttributes($attributes);
        if ($dimensionIdentifier) {
            return $dimensionIdentifier;
        }

        return $this->create($attributes);
    }

    /**
     * @return DimensionIdentifierInterface[]
     */
    public function findByPartialAttributes(array $attributes): array
    {
        $queryBuilder = $this->createQueryBuilder('dimension_identifier');

        $this->addAttributesFilter($queryBuilder, $attributes);

        return $queryBuilder->getQuery()->getScalarResult();
    }

    protected function findOneByAttributes(array $attributes): ?DimensionIdentifierInterface
    {
        $queryBuilder = $this->createQueryBuilder('dimension_identifier')
            ->where('dimension_identifier.attributeCount = ' . \count($attributes));

        $this->addAttributesFilter($queryBuilder, $attributes);

        try {
            return $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }
    }

    protected function addAttributesFilter(QueryBuilder $queryBuilder, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $queryBuilder->join('dimension_identifier.attributes', $key)
                ->andWhere($key . '.value = :' . $key . 'Value')
                ->andWhere($key . '.key = :' . $key . 'Key')
                ->setParameter($key . 'Key', $key)
                ->setParameter($key . 'Value', $value);
        }
    }
}
