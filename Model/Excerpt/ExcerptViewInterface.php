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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt;

interface ExcerptViewInterface
{
    public function getResourceKey(): string;

    public function getResourceId(): string;

    public function getLocale(): string;

    public function getTitle(): ?string;

    public function getMore(): ?string;

    public function getDescription(): ?string;

    public function getCategoriesIds(): array;

    public function getTagNames(): array;

    public function getIconsData(): array;

    public function getImagesData(): array;
}
