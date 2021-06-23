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
            ->innerJoin('dimensionContent.' . $mappingProperty, 'content')
            ->where('content.id = :id')
            ->setParameter('id', $contentRichEntity->getId());

        $this->dimensionContentQueryEnhancer->addSelects(
            $queryBuilder,
            $dimensionContentClass,
            $dimensionAttributes
        );

        /** @var DimensionContentInterface[] $dimensionContents */
        $dimensionContents = $queryBuilder->getQuery()->getResult();

        return new DimensionContentCollection(
            $dimensionContents,
            $dimensionAttributes,
            $dimensionContentClass
        );
    }

    /**
     * Less specific should be returned first to merge correctly.
     *
     * @param mixed[] $attributes
     */
    private function addSortBy(QueryBuilder $queryBuilder, array $attributes): void
    {
        foreach ($attributes as $key => $value) {
            $queryBuilder->addOrderBy('dimensionContent.' . $key);
        }
    }

    /**
     * @param mixed[] $attributes
     */
    private function getAttributesCriteria(string $alias, array $attributes): Criteria
    {
        $criteria = Criteria::create();

        foreach ($attributes as $key => $value) {
            $fieldName = $alias . '.' . $key;
            $expr = $criteria->expr()->isNull($fieldName);

            if (null !== $value) {
                $eqExpr = $criteria->expr()->eq($fieldName, $value);
                $expr = $criteria->expr()->orX($expr, $eqExpr);
            }

            $criteria->andWhere($expr);
        }

        return $criteria;
    }

    /**
     * @param class-string<DimensionContentInterface> $className
     * @param mixed[] $attributes
     *
     * @return mixed[]
     */
    private function getEffectiveAttributes(string $className, array $attributes): array
    {
        $defaultValues = $className::getDefaultDimensionAttributes();

        // Ignore keys that are not part of the default attributes
        $attributes = \array_intersect_key($attributes, $defaultValues);

        $attributes = \array_merge(
            $defaultValues,
            $attributes
        );

        return $attributes;
    }
}
