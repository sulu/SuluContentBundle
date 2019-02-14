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

class DuplicateSeoMessage
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

    public function __construct(string $resourceKey, string $resourceId, string $newResourceId)
    {
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->newResourceId = $newResourceId;
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
}
