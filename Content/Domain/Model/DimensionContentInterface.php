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

interface DimensionContentInterface
{
    public const STAGE_DRAFT = 'draft';
    public const STAGE_LIVE = 'live';

    public static function getResourceKey(): string;

    public function getLocale(): ?string;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function setLocale(?string $locale): void;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function setGhostLocale(?string $ghostLocale): void;

    public function getGhostLocale(): ?string;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function addAvailableLocale(string $availableLocale): void;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function removeAvailableLocale(string $availableLocale): void;

    /**
     * @return string[]|null
     */
    public function getAvailableLocales(): ?array;

    public function getStage(): string;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function setStage(string $stage): void;

    public function getResource(): ContentRichEntityInterface;

    public function isMerged(): bool;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function markAsMerged(): void;

    /**
     * @internal should only be used by content bundle services
     *
     * @return array{
     *     locale: null,
     *     stage: string,
     * }
     */
    public static function getDefaultDimensionAttributes(): array;

    /**
     * @internal should only be used by content bundle services
     *
     * TODO there is an edge case where locale: null is given and locale: null returned by DimensionContentQueryEnhancer.
     *      Find a way to set locale return type by
     *
     * @template T of string|null
     *
     * @param array{
     *     locale: T,
     *     stage?: string|null,
     * } $dimensionAttributes
     *
     * @return array{
     *     locale: T,
     *     stage: string,
     * }
     */
    public static function getEffectiveDimensionAttributes(array $dimensionAttributes): array;
}
