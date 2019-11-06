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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ExcerptTrait
{
    /**
     * @var string|null
     */
    private $excerptTitle;

    /**
     * @var string|null
     */
    private $excerptDescription;

    /**
     * @var string|null
     */
    private $excerptMore;

    /**
     * @var ArrayCollection<CategoryInterface>
     */
    private $excerptCategories;

    /**
     * @var ArrayCollection<TagInterface>
     */
    private $excerptTags;

    /**
     * @var int|null
     */
    private $excerptImage;

    /**
     * @var int|null
     */
    private $excerptIcon;

    public function getExcerptTitle(): ?string
    {
        return $this->excerptTitle;
    }

    public function setExcerptTitle(?string $excerptTitle): void
    {
        $this->excerptTitle = $excerptTitle;
    }

    public function getExcerptDescription(): ?string
    {
        return $this->excerptDescription;
    }

    public function setExcerptDescription(?string $excerptDescription): void
    {
        $this->excerptDescription = $excerptDescription;
    }

    public function getExcerptMore(): ?string
    {
        return $this->excerptMore;
    }

    public function setExcerptMore(?string $excerptMore): void
    {
        $this->excerptMore = $excerptMore;
    }

    /**
     * @return int[]
     */
    public function getExcerptCategories(): array
    {
        $this->initializeCategories();
        $categoryIds = [];
        foreach ($this->excerptCategories as $excerptCategory) {
            $categoryIds[] = $excerptCategory->getId();
        }

        return $categoryIds;
    }

    /**
     * @param CategoryInterface[] $excerptCategories
     */
    public function setExcerptCategories(array $excerptCategories): void
    {
        $this->initializeCategories();
        $this->excerptCategories->clear();

        foreach ($excerptCategories as $excerptCategory) {
            $this->excerptCategories->add($excerptCategory);
        }
    }

    /**
     * @return int[]
     */
    public function getExcerptTags(): array
    {
        $this->initializeTags();
        $tagIds = [];
        foreach ($this->excerptTags as $excerptTag) {
            $tagIds[] = $excerptTag->getId();
        }

        return $tagIds;
    }

    /**
     * @param TagInterface[] $excerptTags
     */
    public function setExcerptTags(array $excerptTags): void
    {
        $this->initializeTags();
        $this->excerptTags->clear();

        foreach ($excerptTags as $excerptTag) {
            $this->excerptTags->add($excerptTag);
        }
    }

    public function getExcerptImage(): ?int
    {
        return $this->excerptImage;
    }

    public function setExcerptImage(?int $excerptImageId): void
    {
        $this->excerptImage = $excerptImageId;
    }

    public function getExcerptIcon(): ?int
    {
        return $this->excerptIcon;
    }

    public function setExcerptIcon(?int $excerptIcon): void
    {
        $this->excerptIcon = $excerptIcon;
    }

    private function initializeTags(): void
    {
        if (null === $this->excerptTags) {
            $this->excerptTags = new ArrayCollection();
        }
    }

    private function initializeCategories(): void
    {
        if (null === $this->excerptCategories) {
            $this->excerptCategories = new ArrayCollection();
        }
    }
}
