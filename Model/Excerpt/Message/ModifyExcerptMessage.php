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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\Message;

use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Common\Payload\PayloadTrait;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;

class ModifyExcerptMessage
{
    use PayloadTrait {
        __construct as protected initializePayload;
    }

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
     * @var ExcerptViewInterface|null
     */
    private $excerpt;

    public function __construct(string $resourceKey, string $resourceId, string $locale, array $payload)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;

        $this->initializePayload($payload);
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
        return $this->getNullableStringValue('title');
    }

    public function getMore(): ?string
    {
        return $this->getNullableStringValue('more');
    }

    public function getDescription(): ?string
    {
        return $this->getNullableStringValue('description');
    }

    public function getCategoryIds(): array
    {
        return $this->getNullableArrayValue('categories') ?? [];
    }

    public function getTagNames(): array
    {
        return $this->getNullableArrayValue('tags') ?? [];
    }

    public function getIconMediaIds(): array
    {
        $iconMediaData = $this->getNullableArrayValue('icons') ?? [];

        return array_key_exists('ids', $iconMediaData) ? $iconMediaData['ids'] : [];
    }

    public function getImageMediaIds(): array
    {
        $imageMediaData = $this->getNullableArrayValue('images') ?? [];

        return array_key_exists('ids', $imageMediaData) ? $imageMediaData['ids'] : [];
    }

    public function getExcerpt(): ExcerptViewInterface
    {
        if (!$this->excerpt) {
            throw new MissingResultException(__METHOD__);
        }

        return $this->excerpt;
    }

    public function setExcerpt(ExcerptViewInterface $excerpt): ModifyExcerptMessage
    {
        $this->excerpt = $excerpt;

        return $this;
    }
}
