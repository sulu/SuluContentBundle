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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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
     * @var class-string<ContentRichEntityInterface>
     */
    protected $contentRichEntityClass;

    /**
     * @var ClassMetadata
     */
    protected $contentRichEntityClassMetadata;

    /**
     * @param bool $showDrafts Inject parameter "sulu_document_manager.show_drafts" here
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     */
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        bool $showDrafts,
        string $contentRichEntityClass
    ) {
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;
        $this->showDrafts = $showDrafts;
        $this->contentRichEntityClass = $contentRichEntityClass;

        $this->contentRichEntityClassMetadata = $this->entityManager->getClassMetadata($this->contentRichEntityClass);
    }

    /**
     * @param array<string, mixed> $filters
     * @param mixed|null $page
     * @param mixed|null $pageSize
     * @param mixed|null $limit
     * @param string $locale
     * @param array<string, mixed> $options
     *
     * @return DimensionContentInterface[]
     */
    public function findByFilters($filters, $page, $pageSize, $limit, $locale, $options = []): array
    {
        $page = null !== $page ? (int) $page : null;
        $pageSize = null !== $pageSize ? (int) $pageSize : null;
        $limit = null !== $limit ? (int) $limit : null;

        $ids = $this->findEntityIdsByFilters($filters, $page, $pageSize, $limit, $locale, $options);

        $contentRichEntities = $this->findEntitiesByIds($ids);

        $showUnpublished = $this->showDrafts;

        return array_filter(
            array_map(
                function (ContentRichEntityInterface $contentRichEntity) use ($locale, $showUnpublished) {
                    $stage = $showUnpublished
                        ? DimensionInterface::STAGE_DRAFT
                        : DimensionInterface::STAGE_LIVE;

                    $resolvedDimensionContent = $this->contentManager->resolve(
                        $contentRichEntity,
                        [
                            'locale' => $locale,
                            'stage' => $stage,
                        ]
                    );

                    $dimension = $resolvedDimensionContent->getDimension();

                    if ($stage !== $dimension->getStage() || $locale !== $dimension->getLocale()) {
                        // TODO FIXME add test or remove this as it should be handled by the ids query
                        return null; // @codeCoverageIgnore
                    }

                    return $resolvedDimensionContent;
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

        $queryBuilder = $this->createEntityIdsQueryBuilder($locale);

        if (!empty($categories = $filters['categories'] ?? [])) {
            $categoryOperator = (string) ($filters['categoryOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->addCategoryFilter($queryBuilder, $categories, $categoryOperator, 'adminCategories')
            );
        }

        if (!empty($websiteCategories = $filters['websiteCategories'] ?? [])) {
            $websiteCategoryOperator = (string) ($filters['websiteCategoriesOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->addCategoryFilter($queryBuilder, $websiteCategories, $websiteCategoryOperator, 'websiteCategories')
            );
        }

        if (!empty($tags = $filters['tags'] ?? [])) {
            $tagOperator = (string) ($filters['tagOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->addTagFilter($queryBuilder, $tags, $tagOperator, 'adminTags')
            );
        }

        if (!empty($websiteTags = $filters['websiteTags'] ?? [])) {
            $websiteTagOperator = (string) ($filters['websiteTagsOperator'] ?? 'OR');

            $parameters = array_merge(
                $parameters,
                $this->addTagFilter($queryBuilder, $websiteTags, $websiteTagOperator, 'websiteTags')
            );
        }

        if (!empty($types = $filters['types'] ?? [])) {
            $parameters = array_merge(
                $parameters,
                $this->addTypeFilter($queryBuilder, $types, 'adminTypes')
            );
        }

        if ($targetGroupId = $filters['targetGroupId'] ?? null) {
            // @codeCoverageIgnoreStart TODO FIXME add testcase for this
            $parameters = array_merge(
                $parameters,
                $this->addTargetGroupFilter($queryBuilder, $targetGroupId, 'targetGroupId')
            );
            // @codeCoverageIgnoreEnd
        }

        if ($dataSource = $filters['dataSource'] ?? null) {
            // @codeCoverageIgnoreStart TODO FIXME add testcase for this
            $includeSubFolders = (bool) ($filters['includeSubFolders'] ?? false);

            $parameters = array_merge(
                $parameters,
                $this->addDatasourceFilter($queryBuilder, (string) $dataSource, $includeSubFolders, 'datasource')
            );
            // @codeCoverageIgnoreEnd
        }

        if ($sortColumn = $filters['sortBy'] ?? null) {
            $sortMethod = (string) ($filters['sortMethod'] ?? 'asc');

            $parameters = array_merge(
                $parameters,
                $this->setSortBy($queryBuilder, (string) $sortColumn, $sortMethod)
            );
        }

        foreach ($parameters as $parameter => $value) {
            $queryBuilder->setParameter($parameter, $value);
        }

        if (null !== $page && $pageSize > 0) {
            $pageOffset = ($page - 1) * $pageSize;
            $restLimit = $limit - $pageOffset;

            // if limitation is smaller than the page size then use the rest limit else use page size plus 1 to
            // determine has next page
            $maxResults = (null !== $limit && $pageSize > $restLimit ? $restLimit : ($pageSize + 1));

            if ($maxResults <= 0) {
                return [];
            }

            $queryBuilder->setMaxResults($maxResults);
            $queryBuilder->setFirstResult($pageOffset);
        } elseif (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        return array_unique(
            array_column($queryBuilder->getQuery()->getScalarResult(), 'id')
        );
    }

    /**
     * Extension point to change field name of category relation.
     */
    protected function getCategoryRelationFieldName(QueryBuilder $queryBuilder): string
    {
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.excerptCategories';
    }

    /**
     * Extension point to change field name of tag relation.
     */
    protected function getTagRelationFieldName(QueryBuilder $queryBuilder): string
    {
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.excerptTags';
    }

    /**
     * Extension point to change field name of target group relation.
     */
    protected function getTargetGroupRelationFieldName(QueryBuilder $queryBuilder): string
    {
        // @codeCoverageIgnoreStart TODO FIXME add testcase for this
        return self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.targetGroups';
        // @codeCoverageIgnoreEnd
    }

    /**
     * Extension point to filter for categories.
     *
     * @param mixed[] $categories
     *
     * @return array<string, mixed> parameters for query
     */
    protected function addCategoryFilter(QueryBuilder $queryBuilder, array $categories, string $categoryOperator, string $alias): array
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
    protected function addTagFilter(QueryBuilder $queryBuilder, array $tags, string $tagOperator, string $alias): array
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
     * Extension point to filter for types.
     *
     * @param mixed[] $types
     *
     * @return array<string, mixed> parameters for query
     */
    protected function addTypeFilter(QueryBuilder $queryBuilder, array $types, string $alias): array
    {
        $queryBuilder->andWhere(static::LOCALIZED_DIMENSION_CONTENT_ALIAS . ".templateKey IN ('" . implode("','", $types) . "')");

        return [];
    }

    /**
     * Extension point to filter for target groups.
     *
     * @param mixed $targetGroupId
     *
     * @return array<string, mixed> parameters for query
     */
    protected function addTargetGroupFilter(QueryBuilder $queryBuilder, $targetGroupId, string $alias): array
    {
        // @codeCoverageIgnoreStart TODO FIXME add testcase for this
        return $this->appendRelation(
            $queryBuilder,
            $this->getTargetGroupRelationFieldName($queryBuilder),
            [$targetGroupId],
            'and',
            $alias
        );
        // @codeCoverageIgnoreEnd
    }

    /**
     * Extension point to filter for datasource.
     *
     * @return array<string, mixed> parameters for query
     */
    protected function addDatasourceFilter(QueryBuilder $queryBuilder, string $datasource, bool $includeSubFolders, string $alias): array
    {
        // @codeCoverageIgnoreStart TODO FIXME add testcase for this
        return [];
        // @codeCoverageIgnoreEnd
    }

    /**
     * Extension point to set order.
     *
     * @return array<string, mixed>
     */
    protected function setSortBy(
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
            $parameters = $this->setSortByJoins($queryBuilder);
        }

        $queryBuilder
            ->addSelect($alias . '.' . $sortColumn)
            ->orderBy($alias . '.' . $sortColumn, $sortMethod);

        return $parameters;
    }

    /**
     * Set sort by joins to query builder for "findIdsByFilters" method.
     *
     * @return array<string, mixed>
     */
    protected function setSortByJoins(QueryBuilder $queryBuilder): array
    {
        // @codeCoverageIgnoreStart TODO FIXME add testcase for this
        return [];
        // @codeCoverageIgnoreEnd
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

    protected function createEntityIdsQueryBuilder(string $locale): QueryBuilder
    {
        $stage = $this->showDrafts
            ? DimensionInterface::STAGE_DRAFT
            : DimensionInterface::STAGE_LIVE;

        return $this->entityManager->createQueryBuilder()
            ->select(self::CONTENT_RICH_ENTITY_ALIAS . '.' . $this->getEntityIdentifierFieldName() . ' as id')
            ->distinct()
            ->from($this->contentRichEntityClass, self::CONTENT_RICH_ENTITY_ALIAS)
            ->innerJoin(self::CONTENT_RICH_ENTITY_ALIAS . '.dimensionContents', self::LOCALIZED_DIMENSION_CONTENT_ALIAS)
            ->innerJoin(self::LOCALIZED_DIMENSION_CONTENT_ALIAS . '.dimension', self::LOCALIZED_DIMENSION_ALIAS)
            ->andWhere(self::LOCALIZED_DIMENSION_ALIAS . '.stage = (:stage)')
            ->andWhere(self::LOCALIZED_DIMENSION_ALIAS . '.locale = (:locale)')
            ->innerJoin(self::CONTENT_RICH_ENTITY_ALIAS . '.dimensionContents', self::UNLOCALIZED_DIMENSION_CONTENT_ALIAS)
            ->innerJoin(self::UNLOCALIZED_DIMENSION_CONTENT_ALIAS . '.dimension', self::UNLOCALIZED_DIMENSION_ALIAS)
            ->andWhere(self::UNLOCALIZED_DIMENSION_ALIAS . '.stage = (:stage)')
            ->andWhere(self::UNLOCALIZED_DIMENSION_ALIAS . '.locale IS NULL')
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

        $entities = $this->entityManager->createQueryBuilder()
            ->select(self::CONTENT_RICH_ENTITY_ALIAS)
            ->from($this->contentRichEntityClass, self::CONTENT_RICH_ENTITY_ALIAS)
            ->where(self::CONTENT_RICH_ENTITY_ALIAS . '.' . $entityIdentifierFieldName . ' IN (:ids)')
            ->getQuery()
            ->setParameter('ids', $ids)
            ->getResult();

        $idPositions = array_flip($ids);

        usort(
            $entities,
            function (ContentRichEntityInterface $a, ContentRichEntityInterface $b) use ($idPositions, $entityIdentifierFieldName) {
                $aId = $this->contentRichEntityClassMetadata->getIdentifierValues($a)[$entityIdentifierFieldName];
                $bId = $this->contentRichEntityClassMetadata->getIdentifierValues($b)[$entityIdentifierFieldName];

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
        return $this->contentRichEntityClassMetadata->getSingleIdentifierFieldName();
    }
}
