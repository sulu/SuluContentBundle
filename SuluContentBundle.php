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

namespace Sulu\Bundle\ContentBundle;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\PersistenceBundle\PersistenceBundleTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SuluContentBundle extends Bundle
{
    use PersistenceBundleTrait;

    public function build(ContainerBuilder $container): void
    {
        $this->buildPersistence(
            [
                DimensionInterface::class => 'sulu.model.dimension.class',
            ],
            $container
        );
    }
}
