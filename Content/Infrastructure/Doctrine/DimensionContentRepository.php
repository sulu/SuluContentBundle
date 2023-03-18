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

    /**
     * @var DimensionContentQueryEnhancer
     */
    private $dimensionContentQueryEnhancer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContentMetadataInspectorInterface $contentMetadataInspector,
        DimensionContentQueryEnhancer $dimensionContentQueryEnhancer
    ) {
        $this->entityManager = $entityManager;
        $this->contentMetadataInspector = $contentMetadataInspector;
        $this->dimensionContentQueryEnhancer = $dimensionContentQueryEnhancer;
    }

    /**
     * @template T of DimensionContentInterface
     *
     * @param ContentRichEntityInterface<T> $contentRichEntity
     * @param mixed[] $dimensionAttributes
     *
     * @return DimensionContentCollectionInterface<T>
     */
    public function load(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): DimensionContentCollectionInterface {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($contentRichEntity));
        $mappingProperty = $this->contentMetadataInspector->getDimensionContentPropertyName(\get_class($contentRichEntity));

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($dimensionContentClass, 'dimensionContent')
            ->where('dimensionContent.' . $mappingProperty . ' = :id')
            ->setParameter('id', $contentRichEntity->getId());

        $this->dimensionContentQueryEnhancer->addSelects(
            $queryBuilder,
            $dimensionContentClass,
            $dimensionAttributes,
            [DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true]
        );

        /** @var T[] $dimensionContents */
        $dimensionContents = $queryBuilder->getQuery()->getResult();

        return new DimensionContentCollection(
            $dimensionContents,
            $dimensionAttributes,
            $dimensionContentClass
        );
    }
}
