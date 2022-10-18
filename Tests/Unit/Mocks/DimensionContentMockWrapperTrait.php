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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Mocks;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

/**
 * Trait for composing a class that wraps a DimensionContentInterface mock.
 *
 * @see MockWrapper to learn why this trait is needed.
 *
 * @property mixed $instance
 */
trait DimensionContentMockWrapperTrait
{
    public static function getResourceKey(): string
    {
        return 'mock-resource-key';
    }

    public function getLocale(): ?string
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->getLocale();
    }

    public function setLocale(?string $locale): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->setLocale($locale);
    }

    public function getGhostLocale(): ?string
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->getGhostLocale();
    }

    public function setGhostLocale(?string $ghostLocale): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->setGhostLocale($ghostLocale);
    }

    public function getAvailableLocales(): ?array
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->getAvailableLocales();
    }

    public function removeAvailableLocale(string $availableLocale): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->removeAvailableLocale($availableLocale);
    }

    public function addAvailableLocale(string $availableLocale): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->addAvailableLocale($availableLocale);
    }

    public function getStage(): string
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->getStage();
    }

    public function setStage(string $stage): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->setStage($stage);
    }

    public function getResource(): ContentRichEntityInterface
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->getResource();
    }

    public function isMerged(): bool
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        return $instance->isMerged();
    }

    public function markAsMerged(): void
    {
        /** @var DimensionContentInterface $instance */
        $instance = $this->instance;

        $instance->markAsMerged();
    }

    public static function getDefaultDimensionAttributes(): array
    {
        return [
            'locale' => null,
            'stage' => 'draft',
        ];
    }

    public static function getEffectiveDimensionAttributes(array $dimensionAttributes): array
    {
        $defaultValues = static::getDefaultDimensionAttributes();

        // Ignore keys that are not part of the default attributes
        $dimensionAttributes = \array_intersect_key($dimensionAttributes, $defaultValues);

        $dimensionAttributes = \array_merge(
            $defaultValues,
            $dimensionAttributes
        );

        return $dimensionAttributes;
    }
}
