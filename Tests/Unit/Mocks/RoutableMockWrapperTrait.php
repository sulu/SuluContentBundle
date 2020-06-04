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

/**
 * Trait for composing a class that wraps a RoutableInterface mock.
 *
 * @see MockWrapper to learn why this trait is needed.
 */
trait RoutableMockWrapperTrait
{
    public function getRoutableResourceKey(): string
    {
        return $this->instance->getRoutableResourceKey();
    }

    public function getRoutableId()
    {
        return $this->instance->getRoutableId();
    }

    public function getRoutableLocale(): ?string
    {
        return $this->instance->getRoutableLocale();
    }
}
