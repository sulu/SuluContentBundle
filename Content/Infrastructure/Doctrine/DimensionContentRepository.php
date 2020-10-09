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
use Sulu\Bundle\ContentBundle\Content\Application\ContentMetadataInspector\ContentMetadataInspectorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;

class DimensionContentRepository implements DimensionContentRepositoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContentMetadataInspectorInterface
     */
    private $contentMetadataInspector;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContentMetadataInspectorInterface $contentMetadataInspector
    ) {
        $this->entityManager = $entityManager;
        $this->contentMetadataInspector = $contentMetadataInspector;
    }

    public function load(
        ContentRichEntityInterface $contentRichEntity,
        DimensionCollectionInterface $dimensionCollection
    ): DimensionContentCollectionInterface {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($contentRichEntity));
        $mappingProperty = $this->contentMetadataInspector->getDimensionContentMappingProperty(\get_class($contentRichEntity));

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($dimensionContentClass, 'dimensionContent')
            ->select('dimensionContent')
            ->addSelect('dimension')
            ->innerJoin('dimensionContent.dimension', 'dimension')
            ->innerJoin('dimensionContent.' . $mappingProperty, 'content')
            ->where('content.id = :id')
            ->setParameter('id', $contentRichEntity->getId());

        $dimensionIds = $dimensionCollection->getDimensionIds();

        $queryBuilder->andWhere($queryBuilder->expr()->in('dimension.id', $dimensionIds));

        /** @var DimensionContentInterface[] $dimensionContents */
        $dimensionContents = [];

        // Sort DimensionContents to reflect order of $dimensionCollection to merge them in the correct order later
        /** @var DimensionContentInterface $dimensionContent */
        foreach ($queryBuilder->getQuery()->getResult() as $dimensionContent) {
            $position = array_search($dimensionContent->getDimension()->getId(), $dimensionIds, true);
            $dimensionContents[$position] = $dimensionContent;
        }

        ksort($dimensionContents);

        return new DimensionContentCollection($dimensionContents, $dimensionCollection);
    }
}
