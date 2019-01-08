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

namespace Sulu\Bundle\ContentBundle\Model\Seo\Factory;

use Sulu\Bundle\ContentBundle\Model\Seo\SeoInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoView;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class SeoViewFactory implements SeoViewFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $seoDimensions, string $locale): ?SeoViewInterface
    {
        $firstDimension = reset($seoDimensions);
        if (!$firstDimension) {
            return null;
        }

        $title = null;
        $description = null;
        $keywords = null;
        $canonicalUrl = null;
        $noIndex = null;
        $noFollow = null;
        $hideInSitemap = null;

        /** @var SeoInterface $seoDimension */
        foreach ($seoDimensions as $seoDimension) {
            $title = $seoDimension->getTitle() ?? $title;
            $description = $seoDimension->getDescription() ?? $description;
            $keywords = $seoDimension->getKeywords() ?? $keywords;
            $canonicalUrl = $seoDimension->getCanonicalUrl() ?? $canonicalUrl;
            $noIndex = $seoDimension->getNoIndex() ?? $noIndex;
            $noFollow = $seoDimension->getNoFollow() ?? $noFollow;
            $hideInSitemap = $seoDimension->getHideInSitemap() ?? $hideInSitemap;
        }

        return new SeoView(
            $firstDimension->getResourceKey(),
            $firstDimension->getResourceId(),
            $locale,
            $title,
            $description,
            $keywords,
            $canonicalUrl,
            $noIndex,
            $noFollow,
            $hideInSitemap
        );
    }
}
