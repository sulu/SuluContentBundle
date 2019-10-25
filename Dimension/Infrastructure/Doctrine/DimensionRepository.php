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
        ?string $locale = null,
        string $workflowStage = DimensionInterface::WORKFLOW_STAGE_DRAFT
    ): DimensionInterface {
        /** @var DimensionInterface $dimension */
        $dimension = new $this->className($id, $locale, $workflowStage);

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
}
