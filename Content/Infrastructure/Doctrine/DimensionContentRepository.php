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

    public function load(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): DimensionContentCollectionInterface {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($contentRichEntity));
        $mappingProperty = $this->contentMetadataInspector->getDimensionContentPropertyName(\get_class($contentRichEntity));

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($dimensionContentClass, 'dimensionContent')
            ->where('IDENTITY(dimensionContent.' . $mappingProperty . ') = :id')
            ->setParameter('id', $contentRichEntity->getId());

        $this->dimensionContentQueryEnhancer->addSelects(
            $queryBuilder,
            $dimensionContentClass,
            $dimensionAttributes,
            [DimensionContentQueryEnhancer::GROUP_SELECT_CONTENT_ADMIN => true]
        );

        /** @var DimensionContentInterface[] $dimensionContents */
        $dimensionContents = $queryBuilder->getQuery()->getResult();

        return new DimensionContentCollection(
            $dimensionContents,
            $dimensionAttributes,
            $dimensionContentClass
        );
    }

    public function getLatestVersion(ContentRichEntityInterface $contentRichEntity): int
    {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($contentRichEntity));
        $mappingProperty = $this->contentMetadataInspector->getDimensionContentPropertyName(\get_class($contentRichEntity));

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($dimensionContentClass, 'dimensionContent')
            ->select('dimensionContent.version')
            ->orderBy('dimensionContent.version', 'DESC')
            ->setMaxResults(1)
            ->where('IDENTITY(dimensionContent.' . $mappingProperty . ') = :id')
            ->setParameter('id', $contentRichEntity->getId());

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getLocales(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): array {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($contentRichEntity));
        $mappingProperty = $this->contentMetadataInspector->getDimensionContentPropertyName(\get_class($contentRichEntity));

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($dimensionContentClass, 'dimensionContent')
            ->select('dimensionContent.locale')
            ->where('IDENTITY(dimensionContent.' . $mappingProperty . ') = :id')
            ->andWhere('dimensionContent.locale IS NOT NULL')
            ->setParameter('id', $contentRichEntity->getId());

        unset($dimensionAttributes['locale']);
        foreach ($dimensionAttributes as $key => $value) {
            $queryBuilder->andWhere('dimensionContent.' . $key . ' = :' . $key)
                ->setParameter(':' . $key, $value);
        }

        return \array_map(function($row) {
            return $row['locale'];
        }, $queryBuilder->getQuery()->getArrayResult());
    }
}
