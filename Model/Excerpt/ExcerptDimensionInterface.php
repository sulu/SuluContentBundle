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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface ExcerptDimensionInterface
{
    public function getDimensionIdentifier(): DimensionIdentifierInterface;

    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getTitle(): ?string;

    public function setTitle(?string $title): self;

    public function getMore(): ?string;

    public function setMore(?string $more): self;

    public function getDescription(): ?string;

    public function setDescription(?string $description): self;

    /**
     * @return CategoryInterface[]
     */
    public function getCategories(): array;

    public function getCategory(int $categoryId): ?CategoryInterface;

    public function addCategory(CategoryInterface $category): self;

    public function removeCategory(CategoryInterface $category): self;

    /**
     * @return TagReferenceInterface[]
     */
    public function getTags(): array;

    public function getTag(string $tagName): ?TagReferenceInterface;

    public function addTag(TagReferenceInterface $tag): self;

    public function removeTag(TagReferenceInterface $tag): self;

    /**
     * @return MediaInterface[]
     */
    public function getIcons(): array;

    public function getIcon(int $mediaId): ?MediaInterface;

    public function addIcon(MediaInterface $icon): self;

    public function removeIcon(MediaInterface $icon): self;

    /**
     * @return MediaInterface[]
     */
    public function getImages(): array;

    public function getImage(int $mediaId): ?MediaInterface;

    public function addImage(MediaInterface $image): self;

    public function removeImage(MediaInterface $image): self;

    public function copyAttributesFrom(ExcerptDimensionInterface $excerptDimension): self;
}
