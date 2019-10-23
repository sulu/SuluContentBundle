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

namespace Sulu\Bundle\ContentBundle\Tests\Application;

use Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle;
use FOS\JsRoutingBundle\FOSJsRoutingBundle;
use Massive\Bundle\BuildBundle\MassiveBuildBundle;
use Sulu\Bundle\ContentBundle\SuluContentBundle;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\ExampleTestBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class Kernel extends SuluTestKernel
{
    public function registerBundles()
    {
        $bundles = parent::registerBundles();
        $bundles[] = new DoctrineFixturesBundle();
        $bundles[] = new MassiveBuildBundle();
        $bundles[] = new SuluContentBundle();
        $bundles[] = new ExampleTestBundle();
        $bundles[] = new FOSJsRoutingBundle();

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config_' . $this->getContext() . '.yml');
    }
}
