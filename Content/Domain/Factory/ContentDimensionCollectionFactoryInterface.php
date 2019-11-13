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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollectionInterface;

interface ContentDimensionCollectionFactoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(
        ContentInterface $content,
        DimensionCollectionInterface $dimensionCollection,
        array $data
    ): ContentDimensionCollectionInterface;
}
