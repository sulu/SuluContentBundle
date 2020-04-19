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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;

class ContentDataProviderRepository implements DataProviderRepositoryInterface
{
    const CONTENT_RICH_ENTITY_ALIAS = 'entity';
    const LOCALIZED_DIMENSION_CONTENT_ALIAS = 'localizedContent';
    const UNLOCALIZED_DIMENSION_CONTENT_ALIAS = 'unlocalizedContent';
    const LOCALIZED_DIMENSION_ALIAS = 'localizedDimension';
    const UNLOCALIZED_DIMENSION_ALIAS = 'unlocalizedDimension';

    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var bool
     */
    protected $showDrafts;

    /**
     * @var string
     */
    protected $entityClassName;

    /**
     * @var ClassMetadata
     */
    protected $entityClassMetadata;

    /**
     * @param bool $showDrafts Inject parameter "sulu_document_manager.show_drafts" here
     * @param class-string<ContentRichEntityInterface> $entityClassName
     */
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        bool $showDrafts,
        string $entityClassName
    ) {
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;
        $this->showDrafts = $showDrafts;
        $this->entityClassName = $entityClassName;

        $this->entityClassMetadata = $this->getEntityManager()->getClassMetadata($this->getEntityClass());
    }

    /**
     * @param array<string, mixed> $filters
     * @param mixed|null $page
     * @param mixed|null $pageSize
     * @param mixed|null $limit
     * @param string $locale
     * @param array<string, mixed> $options
     *
     * @return ContentProjectionInterface[]
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $page = null !== $page ? (int) $page : null;
        $pageSize = null !== $pageSize ? (int) $pageSize : null;
        $limit = null !== $limit ? (int) $limit : null;

        $ids = $this->findEntityIdsByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        $contentRichEntities = $this->findEntitiesByIds($ids);

        $showUnpublished = $this->getShowDrafts();

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
     * @param array<string, mixed> $filters
     * @param array<string, mixed> $options
     *
     * @return mixed[] entity ids
     */
    protected function findEntityIdsByFilters(
        array $filters,
        ?int $page,
        ?int $pageSize,
        ?int $limit,
        string $locale,
        array $options = []
    ): array {
        $parameters = [];

        $queryBuilder = $this->createQueryBuilder($locale);

        if (!empty($categories = $filters['categories'] ?? [])) {
            $categoryOperator = (string) ($filters['categoryOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->appendCategoryRelation($queryBuilder, $categories, $categoryOperator, 'adminCategories')
            );
        }

        if (!empty($websiteCategories = $filters['websiteCategories'] ?? [])) {
            $websiteCategoryOperator = (string) ($filters['websiteCategoryOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->appendCategoryRelation($queryBuilder, $websiteCategories, $websiteCategoryOperator, 'websiteCategories')
            );
        }

        if (!empty($tags = $filters['tags'] ?? [])) {
            $tagOperator = (string) ($filters['tagOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->appendTagRelation($queryBuilder, $tags, $tagOperator, 'adminTags')
            );
        }

        if (!empty($websiteTags = $filters['websiteTags'] ?? [])) {
            $websiteTagOperator = (string) ($filters['websiteTagOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->appendTagRelation($queryBuilder, $websiteTags, $websiteTagOperator, 'websiteTags')
            );
        }

        if ($targetGroupId = $filters['targetGroupId'] ?? null) {
            $parameters = array_merge(
                $parameters,
                $this->appendTargetGroupRelation($queryBuilder, $targetGroupId, 'targetGroupId')
            );
        }

        if ($dataSource = $filters['dataSource'] ?? null) {
            $includeSubFolders = (bool) ($filters['includeSubFolders'] ?? false);

            $parameters = array_merge($parameters, $this->appendDatasource($queryBuilder, (string) $dataSource, $includeSubFolders));
        }

        if ($sortColumn = $filters['sortBy'] ?? null) {
            $sortMethod = (string) ($filters['sortMethod'] ?? 'asc');

            $parameters = array_merge($parameters, $this->appendSortBy($queryBuilder, (string) $sortColumn, $sortMethod));
        }

        $query = $queryBuilder->getQuery();
        foreach ($parameters as $parameter => $value) {
            $query->setParameter($parameter, $value);
        }

        if ($page > 0 && $pageSize > 0) {
            $limit = min($limit, $pageSize) ?? $pageSize;
            $offset = ($page - 1) * $limit;

            $query->setMaxResults($limit);
            $query->setFirstResult($offset);
        } elseif (null !== $limit) {
            $query->setMaxResults($limit);
        }

        return array_unique(
            array_column($query->getScalarResult(), 'id')
        );
    }

    /**
     * Extension point to append relations to category relation if it is not direct linked.
     */
    protected function getCategoryRelationFieldName(QueryBuilder $queryBuilder): string
    {
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.excerptCategories';
    }

    /**
     * Extension point to append relations to tag relation if it is not direct linked.
     */
    protected function getTagRelationFieldName(QueryBuilder $queryBuilder): string
    {
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.excerptTags';
    }

    /**
     * Extension point to append relations to target group relation if it is not direct linked.
     */
    protected function getTargetGroupRelationFieldName(QueryBuilder $queryBuilder): string
    {
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.targetGroups';
    }

    /**
     * Extension point to filter for categories.
     *
     * @param mixed[] $categories
     *
     * @return array<string, mixed> parameters for query
     */
    protected function appendCategoryRelation(QueryBuilder $queryBuilder, array $categories, string $categoryOperator, string $alias): array
    {
        return $this->appendRelation(
            $queryBuilder,
            $this->getCategoryRelationFieldName($queryBuilder),
            $categories,
            mb_strtolower($categoryOperator),
            $alias
        );
    }

    /**
     * Extension point to filter for tags.
     *
     * @param mixed[] $tags
     *
     * @return array<string, mixed> parameters for query
     */
    protected function appendTagRelation(QueryBuilder $queryBuilder, array $tags, string $tagOperator, string $alias): array
    {
        return $this->appendRelation(
            $queryBuilder,
            $this->getTagRelationFieldName($queryBuilder),
            $tags,
            mb_strtolower($tagOperator),
            $alias
        );
    }

    /**
     * Extension point to filter for target groups.
     *
     * @param mixed $targetGroupId
     *
     * @return array<string, mixed> parameters for query
     */
    protected function appendTargetGroupRelation(QueryBuilder $queryBuilder, $targetGroupId, string $alias): array
    {
        return $this->appendRelation(
            $queryBuilder,
            $this->getTargetGroupRelationFieldName($queryBuilder),
            [$targetGroupId],
            'and',
            $alias
        );
    }

    /**
     * Extension point to append datasource.
     *
     * @return array<string, mixed> parameters for query
     */
    protected function appendDatasource(QueryBuilder $queryBuilder, string $datasource, bool $includeSubFolders): array
    {
        return [];
    }

    /**
     * Extension point to append order.
     *
     * @return array<string, mixed>
     */
    protected function appendSortBy(
        QueryBuilder $queryBuilder,
        string $sortColumn,
        string $sortMethod
    ): array {
        $parameters = [];

        $alias = self::LOCALIZED_DIMENSION_CONTENT_ALIAS;

        if (false !== mb_strpos($sortColumn, '.')) {
            list($alias, $sortColumn) = explode('.', $sortColumn, 2);
        }

        if (!\in_array($alias, $queryBuilder->getAllAliases(), true)) {
            $parameters = $this->appendSortByJoins($queryBuilder);
        }

        $queryBuilder
            ->addSelect($alias . '.' . $sortColumn)
            ->orderBy($alias . '.' . $sortColumn, $sortMethod);

        return $parameters;
    }

    /**
     * Append sort by joins to query builder for "findIdsByFilters" method.
     *
     * @return array<string, mixed>
     */
    protected function appendSortByJoins(QueryBuilder $queryBuilder): array
    {
        return [];
    }

    /**
     * Append relation to query builder with given operator.
     *
     * @param mixed[] $values
     *
     * @return array<string, mixed> parameter for the query
     */
    protected function appendRelation(QueryBuilder $queryBuilder, string $relation, array $values, string $operator, string $alias): array
    {
        switch ($operator) {
            case 'or':
                return $this->appendRelationOr($queryBuilder, $relation, $values, $alias);
            case 'and':
                return $this->appendRelationAnd($queryBuilder, $relation, $values, $alias);
        }

        return [];
    }

    /**
     * Append relation to query builder with "or" operator.
     *
     * @param mixed[] $values
     *
     * @return array<string, mixed> parameter for the query
     */
    protected function appendRelationOr(QueryBuilder $queryBuilder, string $relation, array $values, string $alias): array
    {
        $queryBuilder->leftJoin($relation, $alias)
            ->andWhere($alias . '.id IN (:' . $alias . ')');

        return [$alias => $values];
    }

    /**
     * Append relation to query builder with "and" operator.
     *
     * @param mixed[] $values
     *
     * @return array<string, mixed> parameter for the query
     */
    protected function appendRelationAnd(QueryBuilder $queryBuilder, string $relation, array $values, string $alias): array
    {
        $parameter = [];
        $expr = $queryBuilder->expr()->andX();

        foreach ($values as $i => $value) {
            $queryBuilder->leftJoin($relation, $alias . $i);

            $expr->add($queryBuilder->expr()->eq($alias . $i . '.id', ':' . $alias . $i));

            $parameter[$alias . $i] = $value;
        }

        $queryBuilder->andWhere($expr);

        return $parameter;
    }

    protected function createQueryBuilder(string $locale): QueryBuilder
    {
        $stage = $this->getShowDrafts()
            ? DimensionInterface::STAGE_DRAFT
            : DimensionInterface::STAGE_LIVE;

        return $this->getEntityManager()->createQueryBuilder()
            ->select(self::CONTENT_RICH_ENTITY_ALIAS . '.' . $this->getEntityIdentifierFieldName() . ' as id')
            ->distinct()
            ->from($this->getEntityClass(), self::CONTENT_RICH_ENTITY_ALIAS)
            ->innerJoin(self::CONTENT_RICH_ENTITY_ALIAS . '.dimensionContents', self::LOCALIZED_DIMENSION_CONTENT_ALIAS)
            ->innerJoin(self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.dimension', '' . self::LOCALIZED_DIMENSION_ALIAS . '')
            ->andWhere('' . self::LOCALIZED_DIMENSION_ALIAS . '.stage = (:stage)')
            ->andWhere('' . self::LOCALIZED_DIMENSION_ALIAS . '.locale = (:locale)')
            ->innerJoin(self::CONTENT_RICH_ENTITY_ALIAS . '.dimensionContents', self::UNLOCALIZED_DIMENSION_CONTENT_ALIAS)
            ->innerJoin(self::UNLOCALIZED_DIMENSION_CONTENT_ALIAS . '.dimension', '' . self::UNLOCALIZED_DIMENSION_ALIAS . '')
            ->andWhere('' . self::UNLOCALIZED_DIMENSION_ALIAS . '.stage = (:stage)')
            ->andWhere('' . self::UNLOCALIZED_DIMENSION_ALIAS . '.locale IS NULL')
            ->setParameter('stage', $stage)
            ->setParameter('locale', $locale);
    }

    /**
     * @param mixed[] $ids
     *
     * @return ContentRichEntityInterface[]
     */
    protected function findEntitiesByIds(array $ids): array
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
                $aId = $this->getEntityClassMetadata()->getIdentifierValues($a)[$entityIdentifierFieldName];
                $bId = $this->getEntityClassMetadata()->getIdentifierValues($b)[$entityIdentifierFieldName];

                return $idPositions[$aId] - $idPositions[$bId];
            }
        );

        return $entities;
    }

    /**
     * Returns the identifier field name of the ContentRichEntity.
     */
    protected function getEntityIdentifierFieldName(): string
    {
        return $this->getEntityClassMetadata()->getSingleIdentifierFieldName();
    }

    protected function getContentManager(): ContentManagerInterface
    {
        return $this->contentManager;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return class-string<ContentRichEntityInterface>
     */
    protected function getEntityClass(): string
    {
        /** @var class-string<ContentRichEntityInterface> $entityClassName */
        $entityClassName = $this->entityClassName;

        return $entityClassName;
    }

    protected function getEntityClassMetadata(): ClassMetadata
    {
        return $this->entityClassMetadata;
    }

    protected function getShowDrafts(): bool
    {
        return $this->showDrafts;
    }
}
