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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMerger implements MergerInterface
{
    public function merge(object $contentView, object $dimensionContent): void
    {
        if (!$contentView instanceof SeoInterface) {
            return;
        }

        if (!$dimensionContent instanceof SeoInterface) {
            return;
        }

        if ($seoTitle = $dimensionContent->getSeoTitle()) {
            $contentView->setSeoTitle($seoTitle);
        }

        if ($seoDescription = $dimensionContent->getSeoDescription()) {
            $contentView->setSeoDescription($seoDescription);
        }

        if ($seoKeywords = $dimensionContent->getSeoKeywords()) {
            $contentView->setSeoKeywords($seoKeywords);
        }

        if ($seoCanonicalUrl = $dimensionContent->getSeoCanonicalUrl()) {
            $contentView->setSeoCanonicalUrl($seoCanonicalUrl);
        }

        if ($seoNoIndex = $dimensionContent->getSeoNoIndex()) {
            $contentView->setSeoNoIndex($seoNoIndex);
        }

        if ($seoNoFollow = $dimensionContent->getSeoNoFollow()) {
            $contentView->setSeoNoFollow($seoNoFollow);
        }

        if ($seoHideInSitemap = $dimensionContent->getSeoHideInSitemap()) {
            $contentView->setSeoHideInSitemap($seoHideInSitemap);
        }
    }
}
