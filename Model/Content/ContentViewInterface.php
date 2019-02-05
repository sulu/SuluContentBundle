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

interface ContentViewInterface
{
    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getLocale(): string;

    public function getType(): ?string;

    public function getData(): ?array;

    public function withResource(string $resourceKey, string $resourceId, string $locale): self;
}
