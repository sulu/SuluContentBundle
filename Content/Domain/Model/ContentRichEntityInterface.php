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

use Doctrine\Common\Collections\Collection;

interface ContentRichEntityInterface
{
    public static function getResourceKey(): string;

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return Collection<int, DimensionContentInterface>
     */
    public function getDimensionContents(): Collection;

    public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface;

    public function addDimensionContent(DimensionContentInterface $dimensionContent): void;

    public function removeDimensionContent(DimensionContentInterface $dimensionContent): void;
}
