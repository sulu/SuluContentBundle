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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Webmozart\Assert\Assert;

/**
 * TODO add loadGhost functionality
 *      add loadShadow functionality.
 *
 * @final
 */
class DimensionContentQueryEnhancer
{
    /**
     * Withs represents additional selects which can be load to join and select specific sub entities.
     * They are used by groups and fields.
     */
    public const WITH_EXCERPT_TAGS = 'with-excerpt-tags';
    public const WITH_EXCERPT_CATEGORIES = 'with-excerpt-categories';
    public const WITH_EXCERPT_CATEGORIES_TRANSLATIONS = 'with-excerpt-categories-translations';
    public const WITH_EXCERPT_IMAGE = 'with-excerpt-image';
    public const WITH_EXCERPT_IMAGE_TRANSLATIONS = 'with-excerpt-image-translations';
    public const WITH_EXCERPT_ICON = 'with-excerpt-icon';
    public const WITH_EXCERPT_ICON_TRANSLATIONS = 'with-excerpt-icon-translations';

    /**
     * Groups are used in controllers and represents serialization / resolver group,
     * this allows that no controller need to be overwritten when something additonal should be
     * loaded at that endpoint.
     */
    public const GROUP_CONTENT_ADMIN = 'content_admin';
    public const GROUP_CONTENT_WEBSITE = 'content_website';

    /**
     * Fields are used inside smart content and selection content types and match to
     * one or multiple with selects to join a field.
     */
    public const FIELD_EXCERPT_IMAGE = 'excerpt.image';
    public const FIELD_EXCERPT_ICON = 'excerpt.icon';
    public const FIELD_EXCERPT_CATEGORIES = 'excerpt.categories';
    public const FIELD_EXCERPT_TAGS= 'excerpt.tags';

    /**
     * TODO it should be possible to extend fields and groups inside the SELECTS.
     */
    private const SELECTS = [
        // GROUPS
        self::GROUP_CONTENT_ADMIN => [
            self::WITH_EXCERPT_TAGS => true,
            self::WITH_EXCERPT_CATEGORIES => true,
            self::WITH_EXCERPT_IMAGE => true,
            self::WITH_EXCERPT_ICON => true,
        ],
        self::GROUP_CONTENT_WEBSITE => [
            self::WITH_EXCERPT_TAGS => true,
            self::WITH_EXCERPT_CATEGORIES => true,
            self::WITH_EXCERPT_CATEGORIES_TRANSLATIONS => true,
            self::WITH_EXCERPT_IMAGE => true,
            self::WITH_EXCERPT_IMAGE_TRANSLATIONS => true,
            self::WITH_EXCERPT_ICON => true,
            self::WITH_EXCERPT_ICON_TRANSLATIONS => true,
        ],
        // FIELDS
        self::FIELD_EXCERPT_TAGS => [
            self::WITH_EXCERPT_TAGS => true,
        ],
        self::FIELD_EXCERPT_CATEGORIES => [
            self::WITH_EXCERPT_CATEGORIES => true,
            self::WITH_EXCERPT_CATEGORIES_TRANSLATIONS => true,
        ],
        self::FIELD_EXCERPT_IMAGE => [
            self::WITH_EXCERPT_IMAGE => true,
            self::WITH_EXCERPT_IMAGE_TRANSLATIONS => true,
        ],
        self::FIELD_EXCERPT_ICON => [
            self::WITH_EXCERPT_IMAGE => true,
            self::WITH_EXCERPT_IMAGE_TRANSLATIONS => true,
            self::WITH_EXCERPT_ICON => true,
            self::WITH_EXCERPT_ICON_TRANSLATIONS => true,
        ],
    ];

    /**
     * TODO it should be possible to add custom filters for all contents here example when the
     *     excerpt tab and entity get extended with an additional field.
     *
     * @param class-string<DimensionContentInterface> $dimensionContentClassName
     * @param array{
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     * } $filters
     */
    public function addFilters(
        QueryBuilder $queryBuilder,
        string $contentRichEntityAlias,
        string $dimensionContentClassName,
        array $filters
    ): void {
        $effectiveAttributes = $dimensionContentClassName::getEffectiveDimensionAttributes($filters);

        $queryBuilder->leftJoin(
            $contentRichEntityAlias . '.dimensionContents',
            'filterDimensionContent'
        );

        foreach ($effectiveAttributes as $key => $value) {
            if (null === $value) {
                $queryBuilder->andWhere('filterDimensionContent.' . $key . ' IS NULL');

                continue;
            }

            $queryBuilder->andWhere('filterDimensionContent.' . $key . '= :' . $key)
                ->setParameter($key, $value);
        }

        if (is_subclass_of($dimensionContentClassName, ExcerptInterface::class)) {
            $categoryIds = $filters['categoryIds'] ?? null;
            if ($categoryIds) {
                Assert::isArray($categoryIds);

                $this->addJoinFilter(
                    $queryBuilder,
                    'filterDimensionContent.excerptCategories',
                    'filterCategoryId',
                    'id',
                    'categoryIds',
                    $categoryIds,
                    $filters['categoryOperator'] ?? 'OR'
                );
            }

            $categoryKeys = $filters['categoryKeys'] ?? null;
            if ($categoryKeys) {
                Assert::isArray($categoryKeys);

                $this->addJoinFilter(
                    $queryBuilder,
                    'filterDimensionContent.excerptCategories',
                    'filterCategoryId',
                    'key',
                    'categoryKeys',
                    $categoryKeys,
                    $filters['categoryOperator'] ?? 'OR'
                );
            }

            $tagIds = $filters['tagIds'] ?? null;
            if ($tagIds) {
                Assert::isArray($tagIds);

                $this->addJoinFilter(
                    $queryBuilder,
                    'filterDimensionContent.excerptTags',
                    'filterTagId',
                    'id',
                    'tagIds',
                    $tagIds,
                    $filters['tagOperator'] ?? 'OR'
                );
            }

            $tagNames = $filters['tagNames'] ?? null;
            if ($tagNames) {
                Assert::isArray($tagNames);

                $this->addJoinFilter(
                    $queryBuilder,
                    'filterDimensionContent.excerptTags',
                    'filterTagName',
                    'name',
                    'tagNames',
                    $tagNames,
                    $filters['tagOperator'] ?? 'OR'
                );
            }
        }

        if (is_subclass_of($dimensionContentClassName, TemplateInterface::class)) {
            $templateKeys = $filters['templateKeys'] ?? null;
            if ($templateKeys) {
                Assert::isArray($templateKeys);

                $queryBuilder->andWhere('filterDimensionContent.templateKey IN (:templateKeys)')
                    ->setParameter('templateKeys', $templateKeys);
            }
        }
    }

    /**
     * @param int[]|string[] $parameters
     * @param 'AND'|'OR' $operator
     */
    private function addJoinFilter(
        QueryBuilder $queryBuilder,
        string $join,
        string $targetAlias,
        string $targetField,
        string $filterKey,
        array $parameters,
        string $operator = 'OR'
    ): void {
        if ('OR' === $operator) {
            $queryBuilder->leftJoin(
                $join,
                $targetAlias
            );

            $queryBuilder->andWhere($targetAlias . '.' . $targetField . ' IN (:' . $filterKey . ')')
                ->setParameter($filterKey, $parameters);
        } elseif ('AND' === $operator) {
            foreach (array_values($parameters) as $key => $parameter) {
                $queryBuilder->leftJoin(
                    $join,
                    $targetAlias . $key
                );

                $queryBuilder->andWhere($targetAlias . $key . '.' . $targetField . ' = :' . $filterKey . $key)
                    ->setParameter($filterKey . $key, $parameter);
            }
        } else {
            throw new \InvalidArgumentException(
                sprintf('The operator "%s" is not supported for this filter.', $operator)
            );
        }
    }

    /**
     * TODO it should be possible to add custom select for all contents here example when the
     *     excerpt tab and entity get extended with additional relation.
     *
     * @param class-string<DimensionContentInterface> $dimensionContentClassName
     * @param mixed[] $dimensionAttributes
     * @param array<string, bool> $selects
     */
    public function addSelects(
        QueryBuilder $queryBuilder,
        string $dimensionContentClassName,
        array $dimensionAttributes,
        array $selects = []
    ): void {
        foreach ($selects as $selectGroup => $value) {
            if (!$value) {
                continue;
            }

            if (isset(self::SELECTS[$selectGroup])) {
                $selects = \array_merge($selects, self::SELECTS[$selectGroup]);
            }
        }

        $effectiveAttributes = $dimensionContentClassName::getEffectiveDimensionAttributes($dimensionAttributes);
        $this->addSortBy($queryBuilder, $effectiveAttributes);
        $queryBuilder->addCriteria($this->getAttributesCriteria('dimensionContent', $effectiveAttributes));
        $queryBuilder->addSelect('dimensionContent');

        $locale = $dimensionAttributes['locale'] ?? null;

        if (is_subclass_of($dimensionContentClassName, ExcerptInterface::class)) {
            if ($selects[self::WITH_EXCERPT_TAGS] ?? false) {
                $queryBuilder->leftJoin('dimensionContent.excerptTags', 'contentExcerptTag')
                    ->addSelect('contentExcerptTag');
            }

            if ($selects[self::WITH_EXCERPT_CATEGORIES] ?? false) {
                $queryBuilder->leftJoin('dimensionContent.excerptCategories', 'contentExcerptCategory')
                    ->addSelect('contentExcerptCategory');
            }

            if ($selects[self::WITH_EXCERPT_CATEGORIES_TRANSLATIONS] ?? false) {
                Assert::notFalse($selects[self::WITH_EXCERPT_CATEGORIES] ?? false);
                Assert::notNull($locale);
                $queryBuilder->leftJoin(
                    'excerptCategory.translations',
                    'excerptCategoryTranslation',
                    Join::WITH,
                    'excerptCategoryTranslation.locale = IN(contentExcerptCategory.defaultLocale, :locale)'
                )
                    ->addSelect('excerptCategoryTranslation')
                    ->setParameter('locale', $locale);
            }

            if ($selects[self::WITH_EXCERPT_IMAGE] ?? false) {
                $queryBuilder->leftJoin('dimensionContent.excerptImage', 'contentExcerptImage')
                    ->addSelect('contentExcerptImage');
            }

            if ($selects[self::WITH_EXCERPT_IMAGE_TRANSLATIONS] ?? false) {
                Assert::notFalse($selects[self::WITH_EXCERPT_IMAGE]);
                Assert::notNull($locale);
                $queryBuilder->leftJoin('contentExcerptImage.files', 'contentExcerptImageFile')
                    ->addSelect('contentExcerptImageFile');
                $queryBuilder->leftJoin(
                    'contentExcerptImageFile.versions',
                    'contentExcerptImageFileVersion',
                    Join::WITH,
                    'contentExcerptImageFileVersion.version = contentExcerptImageFile.version'
                )
                    ->addSelect('contentExcerptImageFile');
                $queryBuilder->leftJoin(
                    'contentExcerptImageFileVersion.meta',
                    'contentExcerptImageFileVersionMeta',
                    Join::WITH,
                    'contentExcerptImageFileVersionMeta.locale = :locale'
                )
                    ->addSelect('contentExcerptImageFileVersionMeta')
                    ->setParameter('locale', $locale);
                $queryBuilder->leftJoin(
                    'contentExcerptImageFileVersion.defaultMeta',
                    'contentExcerptImageFileVersionDefaultMeta'
                )
                    ->addSelect('contentExcerptImageFileVersionDefaultMeta');
            }

            if ($selects[self::WITH_EXCERPT_ICON] ?? false) {
                $queryBuilder->leftJoin('dimensionContent.excerptIcon', 'contentExcerptIcon')
                    ->addSelect('contentExcerptIcon');
            }

            if ($selects[self::WITH_EXCERPT_ICON_TRANSLATIONS] ?? false) {
                Assert::notFalse($selects[self::WITH_EXCERPT_ICON] ?? false);
                Assert::notNull($locale);
                $queryBuilder->leftJoin('contentExcerptIcon.files', 'contentExcerptIconFile')
                    ->addSelect('contentExcerptIconFile');
                $queryBuilder->leftJoin(
                    'contentExcerptIconFile.versions',
                    'contentExcerptIconFileVersion',
                    Join::WITH,
                    'contentExcerptIconFileVersion.version = contentExcerptIconFile.version'
                )
                    ->addSelect('contentExcerptIconFile');
                $queryBuilder->leftJoin(
                    'contentExcerptIconFileVersion.meta',
                    'contentExcerptIconFileVersionMeta',
                    Join::WITH,
                    'contentExcerptIconFileVersionMeta.locale = :locale'
                )
                    ->addSelect('contentExcerptIconFileVersionMeta')
                    ->setParameter('locale', $locale);
                $queryBuilder->leftJoin(
                    'contentExcerptIconFileVersion.defaultMeta',
                    'contentExcerptIconFileVersionDefaultMeta'
                )
                    ->addSelect('contentExcerptIconFileVersionDefaultMeta');
            }
        }
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
}
