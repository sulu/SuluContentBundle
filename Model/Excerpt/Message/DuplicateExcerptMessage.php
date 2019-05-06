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

class DuplicateExcerptMessage
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
    private $newResourceId;

    /**
     * @var bool
     */
    private $mandatory;

    public function __construct(string $resourceKey, string $resourceId, string $newResourceId, bool $mandatory = true)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->newResourceId = $newResourceId;
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

    public function getNewResourceId(): string
    {
        return $this->newResourceId;
    }

    public function isMandatory(): bool
    {
        return $this->mandatory;
    }
}
