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
     * @var DimensionInterface
     */
    protected $dimension;

    /**
     * @var bool
     */
    private $isMerged = false;

    public function getDimension(): DimensionInterface
    {
        return $this->dimension;
    }

    public function isMerged(): bool
    {
        return $this->isMerged;
    }

    public function markAsMerged(): void
    {
        $this->isMerged = true;
    }
}
