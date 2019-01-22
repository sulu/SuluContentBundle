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

interface IconReferenceRepositoryInterface
{
    public function create(
        ExcerptDimensionInterface $excerptDimension,
        MediaInterface $media,
        int $order = 0
    ): IconReferenceInterface;

    public function remove(IconReferenceInterface $iconReference): void;
}
