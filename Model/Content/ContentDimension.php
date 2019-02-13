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

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

class ContentDimension implements ContentDimensionInterface
{
    /**
     * @var int|null
     */
    private $no;

    /**
     * @var DimensionIdentifierInterface
     */
    private $dimensionIdentifier;

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
    private $type;

    /**
     * @var array
     */
    private $data;

    public function __construct(
        DimensionIdentifierInterface $dimensionIdentifier,
        string $resourceKey,
        string $resourceId,
        ?string $type = null,
        array $data = []
    ) {
        $this->dimensionIdentifier = $dimensionIdentifier;
        $this->resourceKey = $resourceKey;
        $this->resourceId = $resourceId;
        $this->type = $type;
        $this->data = $data;
    }

    public function __clone()
    {
        $this->no = null;
    }

    public function createClone(string $resourceId): ContentDimensionInterface
    {
        $new = clone $this;
        $new->resourceId = $resourceId;

        return $new;
    }

    public function getDimensionIdentifier(): DimensionIdentifierInterface
    {
        return $this->dimensionIdentifier;
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

    public function setType(?string $type): ContentDimensionInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): ContentDimensionInterface
    {
        $this->data = $data;

        return $this;
    }

    public function copyAttributesFrom(ContentDimensionInterface $contentDimension): ContentDimensionInterface
    {
        $this->setType($contentDimension->getType());
        $this->setData($contentDimension->getData());

        return $this;
    }
}
