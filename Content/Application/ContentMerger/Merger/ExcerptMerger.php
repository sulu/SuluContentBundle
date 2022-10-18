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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

/**
 * @internal This class should not be instantiated by a project.
 *           Create your own merger instead.
 */
final class ExcerptMerger implements MergerInterface
{
    public function merge(object $targetObject, object $sourceObject): void
    {
        if (!$targetObject instanceof ExcerptInterface) {
            return;
        }

        if (!$sourceObject instanceof ExcerptInterface) {
            return;
        }

        if ($excerptTitle = $sourceObject->getExcerptTitle()) {
            $targetObject->setExcerptTitle($excerptTitle);
        }

        if ($excerptDescription = $sourceObject->getExcerptDescription()) {
            $targetObject->setExcerptDescription($excerptDescription);
        }

        if ($excerptMore = $sourceObject->getExcerptMore()) {
            $targetObject->setExcerptMore($excerptMore);
        }

        if ($excerptIcon = $sourceObject->getExcerptIcon()) {
            $targetObject->setExcerptIcon($excerptIcon);
        }

        if ($excerptImage = $sourceObject->getExcerptImage()) {
            $targetObject->setExcerptImage($excerptImage);
        }

        if ($excerptTags = $sourceObject->getExcerptTags()) {
            if ([] !== $excerptTags) {
                $targetObject->setExcerptTags($excerptTags);
            }
        }

        if ($excerptCategories = $sourceObject->getExcerptCategories()) {
            if ([] !== $excerptCategories) {
                $targetObject->setExcerptCategories($excerptCategories);
            }
        }
    }
}
