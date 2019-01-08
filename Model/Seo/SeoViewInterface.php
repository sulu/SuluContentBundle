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

interface SeoViewInterface
{
    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getLocale(): string;

    public function getTitle(): ?string;

    public function getDescription(): ?string;

    public function getKeywords(): ?string;

    public function getCanonicalUrl(): ?string;

    public function getNoIndex(): bool;

    public function getNoFollow(): bool;

    public function getHideInSitemap(): bool;
}
