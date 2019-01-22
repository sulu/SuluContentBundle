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

interface ContentDimensionInterface
{
    public function getDimensionIdentifier(): DimensionIdentifierInterface;

    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getType(): ?string;

    public function setType(?string $type): self;

    public function getData(): array;

    public function setData(array $data): self;

    public function copyAttributesFrom(self $contentDimension): self;
}
