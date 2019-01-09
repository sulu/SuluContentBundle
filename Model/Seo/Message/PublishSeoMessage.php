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

namespace Sulu\Bundle\ContentBundle\Model\Seo\Message;

use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class PublishSeoMessage
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
     * @var bool
     */
    private $mandatory;

    /**
     * @var SeoViewInterface|null
     */
    private $seo;

    public function __construct(string $resourceKey, string $resourceId, string $locale, bool $mandatory = true)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;
        $this->mandatory = $mandatory;
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

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }

    public function getSeo(): ?SeoViewInterface
    {
        return $this->seo;
    }

    public function setSeo(SeoViewInterface $seo): PublishSeoMessage
    {
        $this->seo = $seo;

        return $this;
    }
}
