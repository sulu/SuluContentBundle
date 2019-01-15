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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Traits;

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DimensionTrait
{
    protected function findDimension(array $attributes): DimensionInterface
    {
        /** @var DimensionRepositoryInterface */
        $dimensionRepository = $this->getContainer()->get(DimensionRepositoryInterface::class);

        return $dimensionRepository->findOrCreateByAttributes($attributes);
    }

    /**
     * @return ContainerInterface
     */
    abstract protected function getContainer();
}
