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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface ExcerptInterface
{
    public function getExcerptTitle(): ?string;

    public function setExcerptTitle(?string $excerptTitle): void;

    public function getExcerptMore(): ?string;

    public function setExcerptMore(?string $excerptMore): void;

    public function getExcerptDescription(): ?string;

    public function setExcerptDescription(?string $excerptTitle): void;

    /**
     * @return int[]
     */
    public function getExcerptCategoryIds(): array;

    /**
     * @param CategoryInterface[] $excerptCategories
     */
    public function setExcerptCategories(array $excerptCategories): void;

    /**
     * @return int[]
     */
    public function getExcerptTagIds(): array;

    /**
     * @param TagInterface[] $excerptTags
     */
    public function setExcerptTags(array $excerptTags): void;

    public function getExcerptImage(): ?int;

    public function setExcerptImage(?int $excerptImage): void;

    public function getExcerptIcon(): ?int;

    public function setExcerptIcon(?int $excerptIcon): void;

    /**
     * @return mixed[]
     */
    public function excerptToArray(): array;
}
