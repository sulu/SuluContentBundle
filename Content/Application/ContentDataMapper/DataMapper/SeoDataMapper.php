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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoDataMapper implements DataMapperInterface
{
    /**
     * @param array{
     *     seoTitle?: string|null,
     *     seoDescription?: string|null,
     *     seoKeywords?: string|null,
     *     seoCanonicalUrl?: string|null,
     *     seoHideInSitemap?: bool,
     *     seoNoFollow?: bool,
     *     seoNoIndex?: bool,
     * } $data
     */
    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data
    ): void {
        if (!$localizedDimensionContent instanceof SeoInterface) {
            return;
        }

        $localizedDimensionContent->setSeoTitle($data['seoTitle'] ?? null);
        $localizedDimensionContent->setSeoDescription($data['seoDescription'] ?? null);
        $localizedDimensionContent->setSeoKeywords($data['seoKeywords'] ?? null);
        $localizedDimensionContent->setSeoCanonicalUrl($data['seoCanonicalUrl'] ?? null);
        $localizedDimensionContent->setSeoHideInSitemap($data['seoHideInSitemap'] ?? false);
        $localizedDimensionContent->setSeoNoFollow($data['seoNoFollow'] ?? false);
        $localizedDimensionContent->setSeoNoIndex($data['seoNoIndex'] ?? false);
    }
}
