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
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptView implements ExcerptViewInterface
{
    /**
     * @var string
     */
    private $resourceKey;

    /**
     * @var string
     */
    private $resourceId;

    /**
     * @var string
     */
    private $locale;

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
     * @var CategoryInterface[]
     */
    private $categories;

    /**
     * @var TagInterface[]
     */
    private $tags;

    /**
     * @var Media[]
     */
    private $icons;

    /**
     * @var Media[]
     */
    private $images;

    public function __construct(
        string $resourceKey,
        string $resourceId,
        string $locale,
        ?string $title = null,
        ?string $more = null,
        ?string $description = null,
        array $categories = [],
        array $tags = [],
        array $icons = [],
        array $images = []
    ) {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;
        $this->title = $title;
        $this->more = $more;
        $this->description = $description;
        $this->categories = $categories;
        $this->tags = $tags;
        $this->icons = $icons;
        $this->images = $images;
    }

    public function getResourceKey(): string
    {
        return $this->resourceKey;
    }

    public function getResourceId(): string
    {
        return $this->resourceId;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getMore(): ?string
    {
        return $this->more;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategoryIds(): array
    {
        $categoryIds = [];
        foreach ($this->categories as $category) {
            $categoryIds[] = $category->getId();
        }

        return $categoryIds;
    }

    public function getTagNames(): array
    {
        $tagNames = [];
        foreach ($this->tags as $tag) {
            $tagNames[] = $tag->getName();
        }

        return $tagNames;
    }

    public function getIconsData(): array
    {
        $mediaIds = [];
        foreach ($this->icons as $icon) {
            $mediaIds[] = $icon->getId();
        }

        return ['ids' => $mediaIds];
    }

    public function getImagesData(): array
    {
        $mediaIds = [];
        foreach ($this->images as $image) {
            $mediaIds[] = $image->getId();
        }

        return ['ids' => $mediaIds];
    }
}
