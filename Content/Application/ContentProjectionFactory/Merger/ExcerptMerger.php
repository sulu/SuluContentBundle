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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptMerger implements MergerInterface
{
    public function merge(object $contentProjection, object $dimensionContent): void
    {
        if (!$contentProjection instanceof ExcerptInterface) {
            return;
        }

        if (!$dimensionContent instanceof ExcerptInterface) {
            return;
        }

        if ($excerptTitle = $dimensionContent->getExcerptTitle()) {
            $contentProjection->setExcerptTitle($excerptTitle);
        }

        if ($excerptDescription = $dimensionContent->getExcerptDescription()) {
            $contentProjection->setExcerptDescription($excerptDescription);
        }

        if ($excerptMore = $dimensionContent->getExcerptMore()) {
            $contentProjection->setExcerptMore($excerptMore);
        }

        if ($excerptIcon = $dimensionContent->getExcerptIcon()) {
            $contentProjection->setExcerptIcon($excerptIcon);
        }

        if ($excerptImage = $dimensionContent->getExcerptImage()) {
            $contentProjection->setExcerptImage($excerptImage);
        }

        if ($excerptTags = $dimensionContent->getExcerptTags()) {
            if (!empty($excerptTags)) {
                $contentProjection->setExcerptTags($excerptTags);
            }
        }

        if ($excerptCategories = $dimensionContent->getExcerptCategories()) {
            if (!empty($excerptCategories)) {
                $contentProjection->setExcerptCategories($excerptCategories);
            }
        }
    }
}
