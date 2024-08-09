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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Repository;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

/**
 * @template I of int|string
 * @template T of ContentRichEntityInterface
 *
 * @phpstan-type ContentRichEntityRepositoryFilters array{
 *     ids?: I[],
 *     locale?: string|null,
 *     stage?: string|null,
 *     categoryIds?: int[],
 *     categoryKeys?: string[],
 *     categoryOperator?: 'AND'|'OR',
 *     tagIds?: int[],
 *     tagNames?: string[],
 *     tagOperator?: 'AND'|'OR',
 *     templateKeys?: string[],
 *     loadGhost?: bool,
 *     page?: int,
 *     limit?: int,
 * }
 * @phpstan-type ContentRichEntityRepositorySortBys array{
 *     title?: 'asc'|'desc',
 *     authored?: 'asc'|'desc',
 *     workflowPublished?: 'asc'|'desc',
 * }
 * @phpstan-type ContentRichEntityRepositorySelects array{
 *     content_admin?: bool,
 *     content_website?: bool,
 *     with-excerpt-tags?: bool,
 *     with-excerpt-categories?: bool,
 *     with-excerpt-categories-translation?: bool,
 *     with-excerpt-image?: bool,
 *     with-excerpt-image-translation?: bool,
 *     with-excerpt-icon?: bool,
 *     with-excerpt-icon-translation?: bool,
 * }
 */
interface ContentRichEntityRepositoryInterface
{
    /**
     * @param ContentRichEntityRepositoryFilters $filters
     * @param ContentRichEntityRepositorySortBys $sortBys
     * @param ContentRichEntityRepositorySelects $selects
     *
     * @return iterable<T>
     */
    public function findBy(
        array $filters = [],
        array $sortBys = [],
        array $selects = [],
    ): iterable;

    /**
     * @param ContentRichEntityRepositoryFilters $filters
     */
    public function countBy(
        array $filters = [],
    ): int;
}
