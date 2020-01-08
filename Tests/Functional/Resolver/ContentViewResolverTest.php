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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Resolver;

use Sulu\Bundle\ContentBundle\Model\Content\ContentView;
use Sulu\Bundle\ContentBundle\Resolver\ContentViewResolverInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class ContentViewResolverTest extends SuluTestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testResolve(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            '123-123-123',
            'de',
            'default',
            ['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']
        );

        $result = self::getContainer()->get(ContentViewResolverInterface::class)->resolve($contentView);

        $this->assertSame(
            [
                'id' => '123-123-123',
                'template' => 'default',
                'content' => [
                    'title' => 'Sulu',
                    'article' => '<p>Sulu is awesome</p>',
                ],
                'view' => [
                    'title' => [],
                    'article' => [],
                ],
            ],
            $result
        );
    }

    public function testResolveMissingField(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            '123-123-123',
            'de',
            'default',
            ['title' => 'Sulu']
        );

        self::bootKernel();
        $result = self::getContainer()->get(ContentViewResolverInterface::class)->resolve($contentView);

        $this->assertSame(
            [
                'id' => '123-123-123',
                'template' => 'default',
                'content' => [
                    'title' => 'Sulu',
                    'article' => null,
                ],
                'view' => [
                    'title' => [],
                    'article' => [],
                ],
            ],
            $result
        );
    }
}
