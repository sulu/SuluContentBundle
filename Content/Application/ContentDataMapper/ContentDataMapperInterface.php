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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

interface ContentDataMapperInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function map(
        array $data,
        DimensionContentCollectionInterface $dimensionContentCollection
    ): void;
}
