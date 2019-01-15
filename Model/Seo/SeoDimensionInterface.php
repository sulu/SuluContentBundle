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

namespace Sulu\Bundle\ContentBundle\Model\Seo;

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

interface SeoDimensionInterface
{
    public function getDimension(): DimensionInterface;

    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getTitle(): ?string;

    public function setTitle(?string $title): self;

    public function getDescription(): ?string;

    public function setDescription(?string $description): self;

    public function getKeywords(): ?string;

    public function setKeywords(?string $keywords): self;

    public function getCanonicalUrl(): ?string;

    public function setCanonicalUrl(?string $canonicalUrl): self;

    public function getNoIndex(): ?bool;

    public function setNoIndex(?bool $noIndex): self;

    public function getNoFollow(): ?bool;

    public function setNoFollow(?bool $noFollow): self;

    public function getHideInSitemap(): ?bool;

    public function setHideInSitemap(?bool $hideInSitemap): self;

    public function copyAttributesFrom(SeoDimensionInterface $seo): self;
}
