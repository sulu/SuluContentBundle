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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Model;

interface DimensionInterface
{
    public function getId(): string;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): self;

    public function getPublished(): bool;

    public function setPublished(bool $published): self;
}
