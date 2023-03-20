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

/**
 * @template T of ContentRichEntityInterface
 */
interface DimensionContentInterface
{
    public const STAGE_DRAFT = 'draft';
    public const STAGE_LIVE = 'live';

    public static function getResourceKey(): string;

    public function getLocale(): ?string;

    public function setLocale(?string $locale): void;

    public function getStage(): string;

    public function setStage(string $stage): void;

    /**
     * @return T
     */
    public function getResource(): ContentRichEntityInterface;

    public function isMerged(): bool;

    public function markAsMerged(): void;

    /**
     * @return mixed[]
     */
    public static function getDefaultDimensionAttributes(): array;
}
