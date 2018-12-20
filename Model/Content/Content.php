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

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class Content implements ContentInterface
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
     * @var string|null
     */
    private $type;

    /**
     * @var array
     */
    private $data;

    public function __construct(DimensionInterface $dimension, string $resourceKey, string $resourceId, ?string $type = null, array $data = [])
    {
        $this->dimension = $dimension;
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->type = $type;
        $this->data = $data;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): ContentInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): ContentInterface
    {
        $this->data = $data;

        return $this;
    }
}
