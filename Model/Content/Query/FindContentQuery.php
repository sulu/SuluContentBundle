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

namespace Sulu\Bundle\ContentBundle\Model\Content\Query;

use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;

class FindContentQuery
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
     * @var ContentViewInterface|null
     */
    private $content;

    public function __construct(string $resourceKey, string $resourceId, string $locale)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->locale = $locale;
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

    public function getContent(): ContentViewInterface
    {
        if (!$this->content) {
            throw new MissingResultException(__METHOD__);
        }

        return $this->content;
    }

    public function setContent(ContentViewInterface $content): self
    {
        $this->content = $content;

        return $this;
    }
}
