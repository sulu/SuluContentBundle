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
            $categories = array_merge($categories, $excerptDimension->getCategories());
            $tags = array_merge($tags, $excerptDimension->getTags());
            $icons = array_merge($icons, $excerptDimension->getIcons());
            $images = array_merge($images, $excerptDimension->getImages());
        }

        return new ExcerptView(
            $firstDimension->getResourceKey(),
            $firstDimension->getResourceId(),
            $locale,
            $title,
            $more,
            $description,
            array_unique($categories),
            array_unique($tags),
            array_unique($icons),
            array_unique($images)
        );
    }
}
