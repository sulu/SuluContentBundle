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

interface ExcerptDimensionInterface
{
    public function createClone(string $newId): self;

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
     * @return IconReferenceInterface[]
     */
    public function getIcons(): array;

    public function getIcon(int $mediaId): ?IconReferenceInterface;

    public function addIcon(IconReferenceInterface $icon): self;

    public function removeIcon(IconReferenceInterface $icon): self;

    /**
     * @return ImageReferenceInterface[]
     */
    public function getImages(): array;

    public function getImage(int $mediaId): ?ImageReferenceInterface;

    public function addImage(ImageReferenceInterface $image): self;

    public function removeImage(ImageReferenceInterface $image): self;

    public function copyAttributesFrom(self $excerptDimension): self;
}
