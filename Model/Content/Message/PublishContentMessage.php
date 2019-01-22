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

namespace Sulu\Bundle\ContentBundle\Model\Content\Message;

use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;

class PublishContentMessage
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
     * @var ContentViewInterface|null
     */
    private $content;

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

    public function getContent(): ?ContentViewInterface
    {
        return $this->content;
    }

    public function setContent(ContentViewInterface $content): self
    {
        $this->content = $content;

        return $this;
    }
}
