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
    public function merge(object $contentView, object $contentDimension): void
    {
        if (!$contentView instanceof SeoInterface) {
            return;
        }

        if (!$contentDimension instanceof SeoInterface) {
            return;
        }

        if ($seoTitle = $contentDimension->getSeoTitle()) {
            $contentView->setSeoTitle($seoTitle);
        }

        if ($seoDescription = $contentDimension->getSeoDescription()) {
            $contentView->setSeoDescription($seoDescription);
        }

        if ($seoKeywords = $contentDimension->getSeoKeywords()) {
            $contentView->setSeoKeywords($seoKeywords);
        }

        if ($seoCanonicalUrl = $contentDimension->getSeoCanonicalUrl()) {
            $contentView->setSeoCanonicalUrl($seoCanonicalUrl);
        }

        if ($seoNoIndex = $contentDimension->getSeoNoIndex()) {
            $contentView->setSeoNoIndex($seoNoIndex);
        }

        if ($seoNoFollow = $contentDimension->getSeoNoFollow()) {
            $contentView->setSeoNoFollow($seoNoFollow);
        }

        if ($seoHideInSitemap = $contentDimension->getSeoHideInSitemap()) {
            $contentView->setSeoHideInSitemap($seoHideInSitemap);
        }
    }
}
