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

use Sulu\Bundle\AudienceTargetingBundle\SuluAudienceTargetingBundle;
use Sulu\Bundle\AutomationBundle\SuluAutomationBundle;
use Sulu\Bundle\ContentBundle\SuluContentBundle;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\ExampleTestBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Task\TaskBundle\TaskBundle;

class Kernel extends SuluTestKernel
{
    public function registerBundles(): iterable
    {
        $bundles = parent::registerBundles();
        $bundles[] = new SuluContentBundle();
        $bundles[] = new TaskBundle();
        $bundles[] = new SuluAutomationBundle();
        $bundles[] = new ExampleTestBundle();

        foreach ($bundles as $key => $bundle) {
            // Audience Targeting is not configured and so should not be here
            if ($bundle instanceof SuluAudienceTargetingBundle) {
                unset($bundles[$key]);

                break;
            }
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        parent::registerContainerConfiguration($loader);
        $loader->load(__DIR__ . '/config/config_' . $this->getContext() . '.yml');
    }

    protected function getKernelParameters(): array
    {
        $parameters = parent::getKernelParameters();

        $gedmoReflection = new \ReflectionClass(\Gedmo\Exception::class);
        $parameters['gedmo_directory'] = \dirname($gedmoReflection->getFileName());

        return $parameters;
    }
}
