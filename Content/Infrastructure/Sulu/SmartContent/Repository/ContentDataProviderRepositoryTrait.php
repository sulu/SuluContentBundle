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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryTrait;

trait ContentDataProviderRepositoryTrait
{
    use DataProviderRepositoryTrait;

    /**
     * @var ClassMetadata|null
     */
    protected $classMetadata;

    /**
     * @param mixed[] $filters
     * @param mixed|null $page
     * @param mixed|null $pageSize
     * @param mixed|null $limit
     * @param string $locale
     * @param mixed[] $options
     *
     * @return ContentProjectionInterface[]
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $page = null !== $page ? (int) $page : null;
        $pageSize = null !== $pageSize ? (int) $pageSize : null;
        $limit = null !== $limit ? (int) $limit : null;

        $ids = $this->findIdsByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        $contentRichEntities = $this->findByIds($ids);

        $showUnpublished = $this->getShowUnpublished($options);

        return array_filter(
            array_map(
                function (ContentRichEntityInterface $contentRichEntity) use ($locale, $showUnpublished) {
                    $stage = $showUnpublished
                        ? DimensionInterface::STAGE_DRAFT
                        : DimensionInterface::STAGE_LIVE;

                    $contentProjection = $this->getContentManager()->resolve(
                        $contentRichEntity,
                        [
                            'locale' => $locale,
                            'stage' => $stage,
                        ]
                    );

                    $dimension = $contentProjection->getDimension();

                    if ($stage !== $dimension->getStage() || $locale !== $dimension->getLocale()) {
                        return null;
                    }

                    return $contentProjection;
                },
                $contentRichEntities
            )
        );
    }

    /**
     * @param mixed[] $filters
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    protected function findIdsByFilters(
        array $filters,
        ?int $page,
        ?int $pageSize,
        ?int $limit,
        string $locale,
        array $options = []
    ): array {
        /** @var int $intPage */
        $intPage = $page;
        /** @var int $intPageSize */
        $intPageSize = $pageSize;
        /** @var int $intLimit */
        $intLimit = $limit;

        return $this->findByFiltersIds($filters, $intPage, $intPageSize, $intLimit, $locale, $options);
    }

    /**
     * @param string $alias
     */
    protected function appendCategoriesRelation(QueryBuilder $queryBuilder, $alias): string
    {
        return $alias . '.excerptCategories';
    }

    /**
     * @param string $alias
     */
    protected function appendTagsRelation(QueryBuilder $queryBuilder, $alias): string
    {
        return $alias . '.excerptTags';
    }

    /**
     * @param string $alias
     * @param string|null $indexBy
     */
    public function createQueryBuilder($alias, $indexBy = null): QueryBuilder
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->from($this->getDimensionContentClassName(), $alias, $indexBy);
    }

    /**
     * @param string $alias
     * @param string $locale
     */
    protected function appendJoins(QueryBuilder $queryBuilder, $alias, $locale): void
    {
    }

    /**
     * @param string $alias
     * @param string $locale
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    protected function appendSelect(QueryBuilder $queryBuilder, $alias, $locale, $options = []): array
    {
        $showUnpublished = $this->getShowUnpublished($options);

        $queryBuilder
            ->select('IDENTITY(' . $alias . '.' . $this->getEntityRelationName() . ') AS id')
            ->distinct()
            ->innerJoin($alias . '.dimension', 'dimension')
            ->andWhere('dimension.stage = (:dimensionStage)')
            ->andWhere('dimension.locale IS NULL OR dimension.locale = (:dimensionLocale)');

        return [
            'dimensionStage' => $showUnpublished
                ? DimensionInterface::STAGE_DRAFT
                : DimensionInterface::STAGE_LIVE,
            'dimensionLocale' => $locale,
        ];
    }

    /**
     * @param mixed[] $options
     */
    protected function getShowUnpublished(array $options): bool
    {
        if (!\array_key_exists('showUnpublished', $options)) {
            throw new \RuntimeException('Option "showUnpublished" is required. Override "getOptions" in your SmartContentDataProvider.');
        }

        return $options['showUnpublished'] ?: false;
    }

    /**
     * @param mixed[] $ids
     *
     * @return ContentRichEntityInterface[]
     */
    protected function findByIds(array $ids): array
    {
        $entityIdentifierFieldName = $this->getEntityIdentifierFieldName();

        $entities = $this->getEntityManager()->createQueryBuilder()
            ->select('entity')
            ->from($this->getEntityClass(), 'entity')
            ->where('entity.' . $entityIdentifierFieldName . ' IN (:ids)')
            ->getQuery()
            ->setParameter('ids', $ids)
            ->getResult();

        $idPositions = array_flip($ids);

        usort(
            $entities,
            function (ContentRichEntityInterface $a, ContentRichEntityInterface $b) use ($idPositions, $entityIdentifierFieldName) {
                $aId = $this->getClassMetadata()->getIdentifierValues($a)[$entityIdentifierFieldName];
                $bId = $this->getClassMetadata()->getIdentifierValues($b)[$entityIdentifierFieldName];

                return $idPositions[$aId] - $idPositions[$bId];
            }
        );

        return $entities;
    }

    /**
     * @return class-string<DimensionContentInterface>
     */
    protected function getDimensionContentClassName(): string
    {
        $associationMapping = $this->getClassMetadata()->getAssociationMapping('dimensionContents');

        return $associationMapping['targetEntity'];
    }

    protected function getEntityRelationName(): string
    {
        $associationMapping = $this->getClassMetadata()->getAssociationMapping('dimensionContents');

        return $associationMapping['mappedBy'];
    }

    protected function getEntityIdentifierFieldName(): string
    {
        return $this->getClassMetadata()->getSingleIdentifierFieldName();
    }

    protected function getClassMetadata(): ClassMetadata
    {
        if (!$this->classMetadata) {
            $this->classMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());
        }

        return $this->classMetadata;
    }

    abstract protected function getContentManager(): ContentManagerInterface;

    abstract protected function getEntityManager(): EntityManagerInterface;

    /**
     * @return class-string<ContentRichEntityInterface>
     */
    abstract protected function getEntityClass(): string;
}
