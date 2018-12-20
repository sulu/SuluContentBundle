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

namespace Sulu\Bundle\ContentBundle\Model\Content\Factory;

use Sulu\Bundle\ContentBundle\Model\Content\ContentView;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;

class ContentViewFactory implements ContentViewFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function create(array $contentDimensions, string $locale): ?ContentViewInterface
    {
        $firstDimension = reset($contentDimensions);
        if (!$firstDimension) {
            return null;
        }
        $data = [];
        foreach ($contentDimensions as $contentDimension) {
            $data = array_merge($data, $contentDimension->getData());
        }

        return new ContentView(
            $firstDimension->getResourceKey(),
            $firstDimension->getResourceId(),
            $locale,
            $firstDimension->getType(),
            $data
        );
    }
}
