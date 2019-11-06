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

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptMerger implements MergerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function merge(ContentViewInterface $contentView, ContentDimensionInterface $contentDimension): void
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

        if ($excerptTagIds = $contentDimension->getExcerptTags()) {
            $excerptTags = [];
            foreach ($excerptTagIds as $excerptTagId) {
                $tag = $this->entityManager->getPartialReference(TagInterface::class, $excerptTagId);
                if (null !== $tag) {
                    $excerptTags[] = $tag;
                }
            }

            $contentView->setExcerptTags($excerptTags);
        }

        if ($excerptCategoryIds = $contentDimension->getExcerptCategories()) {
            $excerptCategories = [];
            foreach ($excerptCategoryIds as $excerptCategoryId) {
                $category = $this->entityManager->getPartialReference(CategoryInterface::class, $excerptCategoryId);
                if (null !== $category) {
                    $excerptCategories[] = $category;
                }
            }
            $contentView->setExcerptCategories($excerptCategories);
        }
    }
}
