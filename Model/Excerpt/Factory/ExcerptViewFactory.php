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
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;

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
                if (!in_array($dimensionCategory, $categories, true)) {
                    $categories[] = $dimensionCategory;
                }
            }

            // sort tags by order and append to tags array
            $dimensionTags = $excerptDimension->getTags();
            usort($dimensionTags, function(TagReferenceInterface $tag1, TagReferenceInterface $tag2) {
               return $tag1->getOrder() - $tag2->getOrder();
            });
            foreach ($dimensionTags as $dimensionTag) {
                if (!in_array($dimensionTag->getTag(), $tags, true)) {
                    $tags[] = $dimensionTag->getTag();
                }
            }

            foreach ($excerptDimension->getIcons() as $dimensionIcon) {
                if (!in_array($dimensionIcon, $icons, true)) {
                    $icons[] = $dimensionIcon;
                }
            }

            foreach ($excerptDimension->getImages() as $dimensionImage) {
                if (!in_array($dimensionImage, $images, true)) {
                    $images[] = $dimensionImage;
                }
            }
        }

        return new ExcerptView(
            $firstDimension->getResourceKey(),
            $firstDimension->getResourceId(),
            $locale,
            $title,
            $more,
            $description,
            $categories,
            $tags,
            $icons,
            $images
        );
    }
}
