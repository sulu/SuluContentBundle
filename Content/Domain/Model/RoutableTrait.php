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

trait RoutableTrait
{
    public function getRoutableLocale(): ?string
    {
        return $this->getDimension()->getLocale();
    }

    public function getRoutableId()
    {
        return $this->getContentRichEntity()->getId();
    }

    public function getRoutableResourceKey(): string
    {
        return $this->getContentRichEntity()::getResourceKey();
    }

    abstract public function getDimension(): DimensionInterface;

    abstract public function getContentRichEntity(): ContentRichEntityInterface;
}
