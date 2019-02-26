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

namespace Sulu\Bundle\ContentBundle\Model\Content;

class ContentView implements ContentViewInterface
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
     * @var string|null
     */
    private $type;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        string $resourceKey,
        string $resourceId,
        string $locale,
        ?string $type = null,
        array $data = []
    ) {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;
        $this->type = $type;
        $this->data = $data;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function withResource(string $resourceKey, string $resourceId, string $locale): ContentViewInterface
    {
        return new static($resourceKey, $resourceId, $locale, $this->type, $this->data ?? []);
    }
}
