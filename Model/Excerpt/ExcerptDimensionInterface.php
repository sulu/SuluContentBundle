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
use Sulu\Bundle\MediaBundle\Entity\Media;
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

    public function clearCategories(): self;

    public function addCategory(CategoryInterface $category): self;

    /**
     * @return TagInterface[]
     */
    public function getTags(): array;

    public function clearTags(): self;

    public function addTag(TagInterface $tag): self;

    /**
     * @return Media[]
     */
    public function getIcons(): array;

    public function clearIcons(): self;

    public function addIcon(Media $icon): self;

    /**
     * @return Media[]
     */
    public function getImages(): array;

    public function clearImages(): self;

    public function addImage(Media $image): self;

    public function copyAttributesFrom(ExcerptDimensionInterface $excerptDimension): self;
}