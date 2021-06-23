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

trait DimensionContentTrait
{
    /**
     * @var string|null
     */
    protected $locale;

    /**
     * @var string
     */
    protected $stage = DimensionContentInterface::STAGE_DRAFT;

    /**
     * @var bool
     */
    private $isMerged = false;

    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function setStage(string $stage): void
    {
        $this->stage = $stage;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function isMerged(): bool
    {
        return $this->isMerged;
    }

    public function markAsMerged(): void
    {
        $this->isMerged = true;
    }

    public static function getDefaultDimensionAttributes(): array
    {
        return [
            'locale' => null,
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ];
    }

    public static function getEffectiveDimensionAttributes(array $dimensionAttributes): array
    {
        $defaultValues = static::getDefaultDimensionAttributes();

        // Ignore keys that are not part of the default attributes
        $dimensionAttributes = array_intersect_key($dimensionAttributes, $defaultValues);

        $dimensionAttributes = array_merge(
            $defaultValues,
            $dimensionAttributes
        );

        return $dimensionAttributes;
    }
}
