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

namespace Sulu\Bundle\ContentBundle\Content\Application\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;

interface MergerInterface
{
    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    public function merge(ContentDimensionInterface $object, array $data): array;
}
