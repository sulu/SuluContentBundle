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
 * Marker interface for autoloading the doctrine metadata for routables.
 */
interface RoutableInterface
{
    public static function getResourceKey(): string;

    /**
     * @return mixed
     */
    public function getResourceId();

    public function getLocale(): ?string;
}
