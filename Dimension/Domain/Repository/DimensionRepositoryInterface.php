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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Repository;

use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;

interface DimensionRepositoryInterface
{
    public function create(string $id): DimensionInterface;

    public function add(DimensionInterface $directory): void;

    public function remove(DimensionInterface $directory): void;

    /**
     * @param mixed[] $criteria
     */
    public function findOneBy(array $criteria): ?DimensionInterface;

    /**
     * @param mixed[] $criteria
     *
     * @return DimensionInterface[]
     */
    public function findBy(array $criteria): iterable;
}
