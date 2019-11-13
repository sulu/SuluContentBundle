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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptMerger implements MergerInterface
{
    public function merge(object $contentView, object $contentDimension): void
    {
        if (!$contentView instanceof ExcerptInterface) {
            return;
        }

        if (!$contentDimension instanceof ExcerptInterface) {
            return;
        }

        if ($excerptTitle = $contentDimension->getExcerptTitle()) {
            $contentView->setExcerptTitle($excerptTitle);
        }

        if ($excerptDescription = $contentDimension->getExcerptDescription()) {
            $contentView->setExcerptDescription($excerptDescription);
        }

        if ($excerptMore = $contentDimension->getExcerptMore()) {
            $contentView->setExcerptMore($excerptMore);
        }

        if ($excerptIcon = $contentDimension->getExcerptIcon()) {
            $contentView->setExcerptIcon($excerptIcon);
        }

        if ($excerptImage = $contentDimension->getExcerptImage()) {
            $contentView->setExcerptImage($excerptImage);
        }

        if ($excerptTags = $contentDimension->getExcerptTags()) {
            if (!empty($excerptTags)) {
                $contentView->setExcerptTags($excerptTags);
            }
        }

        if ($excerptCategories = $contentDimension->getExcerptCategories()) {
            if (!empty($excerptCategories)) {
                $contentView->setExcerptCategories($excerptCategories);
            }
        }
    }
}
