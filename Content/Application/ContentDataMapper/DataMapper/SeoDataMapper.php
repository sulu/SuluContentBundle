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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoDataMapper implements DataMapperInterface
{
    public function map(
        array $data,
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent
    ): void {
        if (!$localizedDimensionContent instanceof SeoInterface) {
            return;
        }

        $this->setSeoData($localizedDimensionContent, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setSeoData(SeoInterface $dimensionContent, array $data): void
    {
        $dimensionContent->setSeoTitle($data['seoTitle'] ?? null);
        $dimensionContent->setSeoDescription($data['seoDescription'] ?? null);
        $dimensionContent->setSeoKeywords($data['seoKeywords'] ?? null);
        $dimensionContent->setSeoCanonicalUrl($data['seoCanonicalUrl'] ?? null);
        $dimensionContent->setSeoHideInSitemap($data['seoHideInSitemap'] ?? false);
        $dimensionContent->setSeoNoFollow($data['seoNoFollow'] ?? false);
        $dimensionContent->setSeoNoIndex($data['seoNoIndex'] ?? false);
    }
}
