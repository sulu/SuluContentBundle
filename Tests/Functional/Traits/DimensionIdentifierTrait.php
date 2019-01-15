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

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifier;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

trait DimensionIdentifierTrait
{
    protected function findOrCreateDimensionIdentifier(array $attributes): DimensionIdentifierInterface
    {
        /** @var DimensionIdentifierRepositoryInterface */
        $dimensionIdentifierRepository = $this->getEntityManager()->getRepository(DimensionIdentifier::class);

        return $dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
