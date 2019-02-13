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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt;

use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

interface IconReferenceInterface
{
    public function createClone(ExcerptDimensionInterface $excerptDimension): self;

    public function getExcerptDimension(): ExcerptDimensionInterface;

    public function getMedia(): MediaInterface;

    public function getOrder(): int;

    public function setOrder(int $order): self;
}
