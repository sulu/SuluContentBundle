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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Factory;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

interface DimensionContentCollectionFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     * @param array{
     *     locale: string,
     *     stage?: string|null,
     * } $dimensionAttributes
     */
    public function create(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        array $data
    ): DimensionContentCollectionInterface;
}
