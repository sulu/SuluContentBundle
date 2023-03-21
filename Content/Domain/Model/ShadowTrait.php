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

trait ShadowTrait
{
    /**
     * @var string|null
     */
    protected $shadowLocale;

    /**
     * @var string[]|null
     */
    protected $shadowLocales = null;

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function setShadowLocale(?string $shadowLocale): void
    {
        $this->shadowLocale = $shadowLocale;
    }

    public function getShadowLocale(): ?string
    {
        return $this->shadowLocale;
    }

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function addShadowLocale(string $locale, string $shadowLocale): void
    {
        if (null === $this->shadowLocales) {
            $this->shadowLocales = [];
        }

        $this->shadowLocales[$locale] = $shadowLocale;
    }

    /**
     * @internal should only be set by content bundle services not from outside
     */
    public function removeShadowLocale(string $locale): void
    {
        unset($this->shadowLocales[$locale]);
    }

    /**
     * @return array<string, string>
     */
    public function getShadowLocales(): ?array
    {
        return $this->shadowLocales;
    }
}
