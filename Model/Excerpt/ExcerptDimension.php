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
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

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
    private $tagReferences;

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
        $this->tagReferences = new ArrayCollection();
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

    public function clearCategories(): ExcerptDimensionInterface
    {
        $this->categories->clear();

        return $this;
    }

    public function addCategory(CategoryInterface $category): ExcerptDimensionInterface
    {
        $this->categories[] = $category;

        return $this;
    }

    public function getTagReferences(): array
    {
        return $this->tagReferences->getValues();
    }

    public function getTagReferenceByName(string $tagName): ?TagReferenceInterface
    {
        foreach ($this->tagReferences as $tag) {
            if ($tagName === $tag->getTag()->getName()) {
                return $tag;
            }
        }

        return null;
    }

    public function addTagReference(TagReferenceInterface $tag): ExcerptDimensionInterface
    {
        $this->tagReferences->add($tag);

        return $this;
    }

    public function removeTagReference(TagReferenceInterface $tag): ExcerptDimensionInterface
    {
        $this->tagReferences->removeElement($tag);

        return $this;
    }

    public function getIcons(): array
    {
        return $this->icons->getValues();
    }

    public function clearIcons(): ExcerptDimensionInterface
    {
        $this->icons->clear();

        return $this;
    }

    public function addIcon(MediaInterface $icon): ExcerptDimensionInterface
    {
        $this->icons[] = $icon;

        return $this;
    }

    public function getImages(): array
    {
        return $this->images->getValues();
    }

    public function clearImages(): ExcerptDimensionInterface
    {
        $this->images->clear();

        return $this;
    }

    public function addImage(MediaInterface $image): ExcerptDimensionInterface
    {
        $this->images[] = $image;

        return $this;
    }

    public function copyAttributesFrom(
        ExcerptDimensionInterface $excerptDimension,
        TagReferenceRepositoryInterface $tagReferenceRepository
    ): ExcerptDimensionInterface {
        $this->setTitle($excerptDimension->getTitle());
        $this->setMore($excerptDimension->getMore());
        $this->setDescription($excerptDimension->getDescription());

        $this->clearCategories();
        foreach ($excerptDimension->getCategories() as $category) {
            $this->addCategory($category);
        }

        foreach ($this->tagReferences as $currentTagReference) {
            $this->removeTagReference($currentTagReference);
            $tagReferenceRepository->remove($currentTagReference);
        }
        foreach ($excerptDimension->getTagReferences() as $tagReference) {
            $newTagReference = $tagReferenceRepository->create(
                $tagReference->getExcerptDimension(),
                $tagReference->getTag()
            );
            $this->addTagReference($newTagReference);
        }

        $this->clearIcons();
        foreach ($excerptDimension->getIcons() as $icon) {
            $this->addIcon($icon);
        }

        $this->clearImages();
        foreach ($excerptDimension->getImages() as $image) {
            $this->addImage($image);
        }

        return $this;
    }
}
