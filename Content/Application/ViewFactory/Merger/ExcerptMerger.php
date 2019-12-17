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
    public function merge(object $contentView, object $dimensionContent): void
    {
        if (!$contentView instanceof ExcerptInterface) {
            return;
        }

        if (!$dimensionContent instanceof ExcerptInterface) {
            return;
        }

        if ($excerptTitle = $dimensionContent->getExcerptTitle()) {
            $contentView->setExcerptTitle($excerptTitle);
        }

        if ($excerptDescription = $dimensionContent->getExcerptDescription()) {
            $contentView->setExcerptDescription($excerptDescription);
        }

        if ($excerptMore = $dimensionContent->getExcerptMore()) {
            $contentView->setExcerptMore($excerptMore);
        }

        if ($excerptIcon = $dimensionContent->getExcerptIcon()) {
            $contentView->setExcerptIcon($excerptIcon);
        }

        if ($excerptImage = $dimensionContent->getExcerptImage()) {
            $contentView->setExcerptImage($excerptImage);
        }

        if ($excerptTags = $dimensionContent->getExcerptTags()) {
            if (!empty($excerptTags)) {
                $contentView->setExcerptTags($excerptTags);
            }
        }

        if ($excerptCategories = $dimensionContent->getExcerptCategories()) {
            if (!empty($excerptCategories)) {
                $contentView->setExcerptCategories($excerptCategories);
            }
        }
    }
}
