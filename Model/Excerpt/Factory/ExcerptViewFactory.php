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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\Factory;

use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptView;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;

class ExcerptViewFactory implements ExcerptViewFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $excerptDimensions, string $locale): ?ExcerptViewInterface
    {
        $firstDimension = reset($excerptDimensions);
        if (!$firstDimension) {
            return null;
        }

        $title = null;
        $more = null;
        $description = null;
        $categories = [];
        $tags = [];
        $icons = [];
        $images = [];

        /** @var ExcerptDimensionInterface $excerptDimension */
        foreach ($excerptDimensions as $excerptDimension) {
            $title = $excerptDimension->getTitle() ?? $title;
            $more = $excerptDimension->getMore() ?? $more;
            $description = $excerptDimension->getDescription() ?? $description;

            foreach ($excerptDimension->getCategories() as $dimensionCategory) {
                $categories[$dimensionCategory->getId()] = $dimensionCategory;
            }

            foreach ($excerptDimension->getTags() as $dimensionTag) {
                $tags[$dimensionTag->getTag()->getId()] = $dimensionTag;
            }

            foreach ($excerptDimension->getIcons() as $dimensionIcon) {
                $icons[$dimensionIcon->getMedia()->getId()] = $dimensionIcon;
            }

            foreach ($excerptDimension->getImages() as $dimensionImage) {
                $images[$dimensionImage->getMedia()->getId()] = $dimensionImage;
            }
        }

        return new ExcerptView(
            $firstDimension->getResourceKey(),
            $firstDimension->getResourceId(),
            $locale,
            $title,
            $more,
            $description,
            array_values($categories),
            array_values($tags),
            array_values($icons),
            array_values($images)
        );
    }
}
