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

interface DimensionContentInterface
{
    /**
     * @return mixed
     */
    public function getId();

    public function getDimension(): DimensionInterface;

    public function createProjectionInstance(): ContentProjectionInterface;
}
