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
    private $excerptMore;

    /**
     * @var string|null
     */
    private $excerptDescription;

    /**
     * @var CategoryInterface[]
     */
    private $excerptCategories = [];

    /**
     * @var TagInterface[]
     */
    private $excerptTags = [];

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

    public function getExcerptMore(): ?string
    {
        return $this->excerptMore;
    }

    public function setExcerptMore(?string $excerptMore): void
    {
        $this->excerptMore = $excerptMore;
    }

    public function getExcerptDescription(): ?string
    {
        return $this->excerptDescription;
    }

    public function setExcerptDescription(?string $excerptDescription): void
    {
        $this->excerptDescription = $excerptDescription;
    }

    /**
     * @return int[]
     */
    public function getExcerptCategoryIds(): array
    {
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
        $this->excerptCategories = $excerptCategories;
    }

    /**
     * @return int[]
     */
    public function getExcerptTagIds(): array
    {
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
        $this->excerptTags = $excerptTags;
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

    /**
     * @return mixed[]
     */
    public function excerptToArray(): array
    {
        return [
            'title' => $this->getExcerptTitle(),
            'description' => $this->getExcerptDescription(),
            'more' => $this->getExcerptMore(),
            'image' => $this->getExcerptImage(),
            'icon' => $this->getExcerptIcon(),
            'categories' => $this->getExcerptCategoryIds(),
            'tags' => $this->getExcerptTagIds(),
        ];
    }
}
