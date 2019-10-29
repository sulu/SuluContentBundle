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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\DependencyInjection\Configuration;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testObjects(): void
    {
        $this->assertConfigurationIsValid([
            'sulu_content' => [
                'objects' => [
                    'dimension' => [
                        'model' => 'TestModel',
                        'repository' => 'TestRepository',
                    ],
                ],
            ],
        ]);
    }

    public function testObjectsOnlyModel(): void
    {
        $this->assertConfigurationIsValid([
            'sulu_content' => [
                'objects' => [
                    'dimension' => [
                        'model' => 'TestModel',
                    ],
                ],
            ],
        ]);
    }

    public function testObjectsOnlyRepository(): void
    {
        $this->assertConfigurationIsValid([
            'sulu_content' => [
                'objects' => [
                    'dimension' => [
                        'repository' => 'TestRepository',
                    ],
                ],
            ],
        ]);
    }
}
