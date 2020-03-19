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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMerger implements MergerInterface
{
    public function merge(object $contentProjection, object $dimensionContent): void
    {
        if (!$contentProjection instanceof SeoInterface) {
            return;
        }

        if (!$dimensionContent instanceof SeoInterface) {
            return;
        }

        if ($seoTitle = $dimensionContent->getSeoTitle()) {
            $contentProjection->setSeoTitle($seoTitle);
        }

        if ($seoDescription = $dimensionContent->getSeoDescription()) {
            $contentProjection->setSeoDescription($seoDescription);
        }

        if ($seoKeywords = $dimensionContent->getSeoKeywords()) {
            $contentProjection->setSeoKeywords($seoKeywords);
        }

        if ($seoCanonicalUrl = $dimensionContent->getSeoCanonicalUrl()) {
            $contentProjection->setSeoCanonicalUrl($seoCanonicalUrl);
        }

        if ($seoNoIndex = $dimensionContent->getSeoNoIndex()) {
            $contentProjection->setSeoNoIndex($seoNoIndex);
        }

        if ($seoNoFollow = $dimensionContent->getSeoNoFollow()) {
            $contentProjection->setSeoNoFollow($seoNoFollow);
        }

        if ($seoHideInSitemap = $dimensionContent->getSeoHideInSitemap()) {
            $contentProjection->setSeoHideInSitemap($seoHideInSitemap);
        }
    }
}
