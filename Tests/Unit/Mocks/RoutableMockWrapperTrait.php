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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;

/**
 * Trait for composing a class that wraps a RoutableInterface mock.
 *
 * @see MockWrapper to learn why this trait is needed.
 *
 * @property mixed $instance
 */
trait RoutableMockWrapperTrait
{
    public static function getResourceKey(): string
    {
        return 'mock-resource-key';
    }

    public function getResourceId()
    {
        /** @var RoutableInterface $instance */
        $instance = $this->instance;

        return $instance->getResourceId();
    }

    public function getLocale(): ?string
    {
        /** @var RoutableInterface $instance */
        $instance = $this->instance;

        return $instance->getLocale();
    }
}
