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

namespace Sulu\Bundle\ContentBundle\Dimension\Infrastructure\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;

class DimensionRepository implements DimensionRepositoryInterface
{
    /**
     * @var string
     */
    private $className;

    /**
     * @var ObjectRepository
     */
    protected $entityRepository;

    /**
     * @var ObjectManager
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $em, ClassMetadata $class)
    {
        $this->entityRepository = new EntityRepository($em, $class);
        $this->entityManager = $em;
        $this->className = $this->entityRepository->getClassName();
    }

    public function create(
        ?string $id = null,
        array $attributes = []
    ): DimensionInterface {
        /** @var DimensionInterface $dimension */
        $dimension = new $this->className($id, $attributes);

        return $dimension;
    }

    public function remove(DimensionInterface $dimension): void
    {
        $this->entityManager->remove($dimension);
    }

    public function add(DimensionInterface $dimension): void
    {
        $this->entityManager->persist($dimension);
    }

    public function findIdsByAttributes(array $attributes): array
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('dimension')
            ->select('dimension.id');

        $attributes = $this->getAttributes($attributes);
        $queryBuilder->addCriteria($this->getAttributesCriteria($attributes));

        // Less specific should be returned first to merge correctly
        foreach ($this->getAttributes($attributes) as $key => $value) {
            $queryBuilder->addOrderBy('dimension.' . $key);
        }

        return array_map(function(array $item) {
            return $item['id'];
        }, $queryBuilder->getQuery()->getScalarResult());
    }


    public function findOneBy(array $criteria): ?DimensionInterface
    {
        /** @var DimensionInterface|null $directory */
        $directory = $this->entityRepository->findOneBy($criteria);

        return $directory;
    }

    public function findBy(array $criteria): iterable
    {
        /** @var DimensionInterface[] $directory */
        $directories = $this->entityRepository->findBy($criteria);

        return $directories;
    }

    private function getAttributesCriteria(array $attributes): Criteria
    {
        $criteria = Criteria::create();

        foreach ($attributes as $key => $value) {
            $fieldName = 'dimension.' . $key;
            $expr = $criteria->expr()->isNull($fieldName);

            if ($value !== null) {
                $eqExpr = $criteria->expr()->eq($fieldName, $value);
                $expr = $criteria->expr()->orX($expr, $eqExpr);
            }

            $criteria->andWhere($expr);
        }

        return $criteria;
    }

    /**
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    private function getAttributes(array $attributes): array
    {
        $defaultValues = $this->className::getDefaultValues();

        $attributes = array_merge(
            $defaultValues,
            $attributes
        );

        unset($attributes['id']);
        unset($attributes['no']);

        return $attributes;
    }
}
