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
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

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
     * @var Collection|TagInterface[]
     */
    private $tags;

    /**
     * @var Collection|Media[]
     */
    private $icons;

    /**
     * @var Collection|Media[]
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

    public function addCategory(CategoryInterface $category): ExcerptDimensionInterface
    {
        $this->categories[] = $category;

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

    public function addTag(TagInterface $tag): ExcerptDimensionInterface
    {
        $this->tags[] = $tag;

        return $this;
    }

    public function removeTag(TagInterface $tag): ExcerptDimensionInterface
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getIcons(): array
    {
        return $this->icons->getValues();
    }

    public function addIcon(Media $icon): ExcerptDimensionInterface
    {
        $this->icons[] = $icon;

        return $this;
    }

    public function removeIcon(Media $icon): ExcerptDimensionInterface
    {
        $this->icons->removeElement($icon);

        return $this;
    }

    public function getImages(): array
    {
        return $this->images->getValues();
    }

    public function addImage(Media $image): ExcerptDimensionInterface
    {
        $this->images[] = $image;

        return $this;
    }

    public function removeImage(Media $image): ExcerptDimensionInterface
    {
        $this->images->removeElement($image);

        return $this;
    }

    public function copyAttributesFrom(ExcerptDimensionInterface $excerptDimension): ExcerptDimensionInterface
    {
        $this->setTitle($excerptDimension->getTitle());
        $this->setDescription($excerptDimension->getDescription());
        $this->setMore($excerptDimension->getMore());

        $this->categories->clear();
        foreach ($excerptDimension->getCategories() as $category) {
            $this->addCategory($category);
        }

        $this->tags->clear();
        foreach ($excerptDimension->getTags() as $tag) {
            $this->addTag($tag);
        }

        $this->icons->clear();
        foreach ($excerptDimension->getIcons() as $icon) {
            $this->addIcon($icon);
        }

        $this->images->clear();
        foreach ($excerptDimension->getImages() as $image) {
            $this->addImage($image);
        }
    }
}
