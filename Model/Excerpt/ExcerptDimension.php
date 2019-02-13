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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

class ExcerptDimension implements ExcerptDimensionInterface
{
    /**
     * @var int
     */
    private $no;

    /**
     * @var DimensionIdentifierInterface
     */
    private $dimensionIdentifier;

    /**
     * @var string
     */
    private $resourceKey;

    /**
     * @var string
     */
    private $resourceId;

    /**
     * @var ?string
     */
    private $title;

    /**
     * @var ?string
     */
    private $more;

    /**
     * @var ?string
     */
    private $description;

    /**
     * @var Collection|CategoryInterface[]
     */
    private $categories;

    /**
     * @var Collection|TagReferenceInterface[]
     */
    private $tags;

    /**
     * @var Collection|IconReferenceInterface[]
     */
    private $icons;

    /**
     * @var Collection|ImageReferenceInterface[]
     */
    private $images;

    public function __construct(
        DimensionIdentifierInterface $dimensionIdentifier,
        string $resourceKey,
        string $resourceId,
        ?string $title = null,
        ?string $more = null,
        ?string $description = null
    ) {
        $this->dimensionIdentifier = $dimensionIdentifier;
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->title = $title;
        $this->more = $more;
        $this->description = $description;
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->icons = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function __clone()
    {
        $this->no = null;
        $this->categories = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->icons = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    public function createClone(string $resourceId): ExcerptDimensionInterface
    {
        $new = clone $this;
        $new->resourceId = $resourceId;

        foreach ($this->categories as $category) {
            $new->addCategory($category);
        }

        foreach ($this->tags as $tagReference) {
            $newTagReference = $tagReference->createClone($new);
            $new->addTag($newTagReference);
        }

        foreach ($this->icons as $iconReference) {
            $newIconReference = $iconReference->createClone($new);
            $new->addIcon($newIconReference);
        }

        foreach ($this->images as $imageReference) {
            $newImageReference = $imageReference->createClone($new);
            $new->addImage($newImageReference);
        }

        return $new;
    }

    public function getDimensionIdentifier(): DimensionIdentifierInterface
    {
        return $this->dimensionIdentifier;
    }

    public function getResourceKey(): string
    {
        return $this->resourceKey;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): ExcerptDimensionInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getMore(): ?string
    {
        return $this->more;
    }

    public function setMore(?string $more): ExcerptDimensionInterface
    {
        $this->more = $more;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): ExcerptDimensionInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getCategories(): array
    {
        return $this->categories->getValues();
    }

    public function getCategory(int $categoryId): ?CategoryInterface
    {
        if ($this->categories->containsKey($categoryId)) {
            return $this->categories->get($categoryId);
        }

        return null;
    }

    public function addCategory(CategoryInterface $category): ExcerptDimensionInterface
    {
        $this->categories->set($category->getId(), $category);

        return $this;
    }

    public function removeCategory(CategoryInterface $category): ExcerptDimensionInterface
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags->getValues();
    }

    public function getTag(string $tagName): ?TagReferenceInterface
    {
        foreach ($this->tags as $tag) {
            if ($tagName === $tag->getTag()->getName()) {
                return $tag;
            }
        }

        return null;
    }

    public function addTag(TagReferenceInterface $tag): ExcerptDimensionInterface
    {
        $this->tags->set($tag->getTag()->getId(), $tag);

        return $this;
    }

    public function removeTag(TagReferenceInterface $tag): ExcerptDimensionInterface
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getIcons(): array
    {
        return $this->icons->getValues();
    }

    public function getIcon(int $mediaId): ?IconReferenceInterface
    {
        if ($this->icons->containsKey($mediaId)) {
            return $this->icons->get($mediaId);
        }

        return null;
    }

    public function addIcon(IconReferenceInterface $icon): ExcerptDimensionInterface
    {
        $this->icons->set($icon->getMedia()->getId(), $icon);

        return $this;
    }

    public function removeIcon(IconReferenceInterface $icon): ExcerptDimensionInterface
    {
        $this->icons->removeElement($icon);

        return $this;
    }

    public function getImages(): array
    {
        return $this->images->getValues();
    }

    public function getImage(int $mediaId): ?ImageReferenceInterface
    {
        if ($this->images->containsKey($mediaId)) {
            return $this->images->get($mediaId);
        }

        return null;
    }

    public function addImage(ImageReferenceInterface $image): ExcerptDimensionInterface
    {
        $this->images->set($image->getMedia()->getId(), $image);

        return $this;
    }

    public function removeImage(ImageReferenceInterface $image): ExcerptDimensionInterface
    {
        $this->images->removeElement($image);

        return $this;
    }

    public function copyAttributesFrom(ExcerptDimensionInterface $excerptDimension): ExcerptDimensionInterface
    {
        $this->setTitle($excerptDimension->getTitle());
        $this->setMore($excerptDimension->getMore());
        $this->setDescription($excerptDimension->getDescription());

        return $this;
    }
}
