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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;

class ContentDimensionRepository implements ContentDimensionRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function load(
        ContentInterface $content,
        DimensionCollectionInterface $dimensionCollection
    ): ContentDimensionCollectionInterface {
        $classMetadata = $this->entityManager->getClassMetadata(\get_class($content));
        $associationMapping = $classMetadata->getAssociationMapping('dimensions');
        $contentDimensionClass = $associationMapping['targetEntity'];

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($contentDimensionClass, 'contentDimension')
            ->select('contentDimension')
            ->addSelect('dimension')
            ->innerJoin('contentDimension.dimension', 'dimension')
            ->innerJoin('contentDimension.' . $associationMapping['mappedBy'], 'content')
            ->where('content.id = :id')
            ->setParameter('id', $content->getId());

        $dimensionIds = $dimensionCollection->getDimensionIds();

        $queryBuilder->andWhere($queryBuilder->expr()->in('dimension.id', $dimensionIds));

        /** @var ContentDimensionInterface[] $contentDimensions */
        $contentDimensions = [];

        // Sort correctly ContentDimensions by given dimensionIds to merge them later correctly
        /** @var ContentDimensionInterface $contentDimension */
        foreach ($queryBuilder->getQuery()->getResult() as $contentDimension) {
            $position = array_search($contentDimension->getDimension()->getId(), $dimensionIds, true);
            $contentDimensions[$position] = $contentDimension;
        }

        ksort($contentDimensions);

        return new ContentDimensionCollection($contentDimensions, $dimensionCollection);
    }
}
