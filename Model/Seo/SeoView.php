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

class SeoView implements SeoViewInterface
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
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $keywords;

    /**
     * @var string
     */
    private $canonicalUrl;

    /**
     * @var bool
     */
    private $noIndex;

    /**
     * @var bool
     */
    private $noFollow;

    /**
     * @var bool
     */
    private $hideInSitemap;

    public function __construct(
        string $resourceKey,
        string $resourceId,
        string $locale,
        string $title,
        string $description,
        string $keywords,
        string $canonicalUrl,
        bool $noIndex = false,
        bool $noFollow = false,
        bool $hideInSitemap = false
    ){
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;
        $this->title = $title;
        $this->description = $description;
        $this->keywords = $keywords;
        $this->canonicalUrl = $canonicalUrl;
        $this->noIndex = $noIndex;
        $this->noFollow = $noFollow;
        $this->hideInSitemap = $hideInSitemap;
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getKeywords(): string
    {
        return $this->keywords;
    }

    public function getCanonicalUrl(): string
    {
        return $this->canonicalUrl;
    }

    public function getNoIndex(): bool
    {
        return $this->noIndex;
    }

    public function getNoFollow(): bool
    {
        return $this->noFollow;
    }

    public function getHideInSitemap(): bool
    {
        return $this->hideInSitemap;
    }
}
