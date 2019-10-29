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

namespace Sulu\Bundle\ContentBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\SuluContentBundle;
use Sulu\Bundle\PersistenceBundle\DependencyInjection\Compiler\ResolveTargetEntitiesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SuluContentBundleTest extends TestCase
{
    protected function getBundle(): SuluContentBundle
    {
        return new SuluContentBundle();
    }

    public function testPersistenceCompilerPass(): void
    {
        $bundle = $this->getBundle();
        $containerBuilder = new ContainerBuilder();

        $bundle->build($containerBuilder);
        $passConfig = $containerBuilder->getCompiler()->getPassConfig();
        $compilerPass = null;
        foreach ($passConfig->getPasses() as $pass) {
            if ($pass instanceof ResolveTargetEntitiesPass) {
                $compilerPass = $pass;

                break;
            }
        }

        $this->assertInstanceOf(ResolveTargetEntitiesPass::class, $compilerPass);
    }
}
