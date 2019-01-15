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

namespace Sulu\Bundle\ContentBundle\Model\Seo;

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class Seo implements SeoInterface
{
    /**
     * @var DimensionInterface
     */
    private $dimension;

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
    private $description;

    /**
     * @var ?string
     */
    private $keywords;

    /**
     * @var ?string
     */
    private $canonicalUrl;

    /**
     * @var ?bool
     */
    private $noIndex;

    /**
     * @var ?bool
     */
    private $noFollow;

    /**
     * @var ?bool
     */
    private $hideInSitemap;

    public function __construct(
        DimensionInterface $dimension,
        string $resourceKey,
        string $resourceId,
        string $title = null,
        string $description = null,
        string $keywords = null,
        string $canonicalUrl = null,
        bool $noIndex = null,
        bool $noFollow = null,
        bool $hideInSitemap = null
    ) {
        $this->dimension = $dimension;
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->canonicalUrl = $canonicalUrl;
        $this->noIndex = $noIndex;
        $this->noFollow = $noFollow;
        $this->hideInSitemap = $hideInSitemap;
    }

    public function getDimension(): DimensionInterface
    {
        return $this->dimension;
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

    public function setTitle(?string $title): SeoInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): SeoInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(?string $keywords): SeoInterface
    {
        $this->keywords = $keywords;

        return $this;
    }

    public function getCanonicalUrl(): ?string
    {
        return $this->canonicalUrl;
    }

    public function setCanonicalUrl(?string $canonicalUrl): SeoInterface
    {
        $this->canonicalUrl = $canonicalUrl;

        return $this;
    }

    public function getNoIndex(): ?bool
    {
        return $this->noIndex;
    }

    public function setNoIndex(?bool $noIndex): SeoInterface
    {
        $this->noIndex = $noIndex;

        return $this;
    }

    public function getNoFollow(): ?bool
    {
        return $this->noFollow;
    }

    public function setNoFollow(?bool $noFollow): SeoInterface
    {
        $this->noFollow = $noFollow;

        return $this;
    }

    public function getHideInSitemap(): ?bool
    {
        return $this->hideInSitemap;
    }

    public function setHideInSitemap(?bool $hideInSitemap): SeoInterface
    {
        $this->hideInSitemap = $hideInSitemap;

        return $this;
    }

    public function copyAttributesFrom(SeoInterface $seo): SeoInterface
    {
        $this->setTitle($seo->getTitle());
        $this->setDescription($seo->getDescription());
        $this->setKeywords($seo->getKeywords());
        $this->setCanonicalUrl($seo->getCanonicalUrl());
        $this->setNoIndex($seo->getNoIndex());
        $this->setNoFollow($seo->getNoFollow());
        $this->setHideInSitemap($seo->getHideInSitemap());

        return $this;
    }
}
