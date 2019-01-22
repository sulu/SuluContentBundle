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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\Factory;

use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;

interface ExcerptViewFactoryInterface
{
    /**
     * @param ExcerptDimensionInterface[] $excerptDimensions
     */
    public function create(array $excerptDimensions, string $locale): ?ExcerptViewInterface;
}
