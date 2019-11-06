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

trait ContentViewTrait
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $dimensionId;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDimensionId(): string
    {
        return $this->dimensionId;
    }
}
