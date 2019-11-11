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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper;

interface MapperInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function map(
        array $data,
        object $contentDimension,
        ?object $localizedContentDimension = null
    ): void;
}
