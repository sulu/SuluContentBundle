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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentView;

class ContentViewTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testGetResourceKey(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $this->assertSame(self::RESOURCE_KEY, $contentView->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $this->assertSame('resource-1', $contentView->getResourceId());
    }

    public function testGetLocale(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $this->assertSame('en', $contentView->getLocale());
    }

    public function testGetType(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $this->assertSame('type-1', $contentView->getType());
    }

    public function testGetData(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $this->assertSame(['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>'], $contentView->getData());
    }

    public function testWithResource(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['title' => 'Sulu', 'url' => '/test', 'article' => '<p>Sulu is awesome</p>']
        );

        $result = $contentView->withResource('product', '123-123-123', 'en');
        $this->assertNotSame($contentView, $result);
        $this->assertSame('product', $result->getResourceKey());
        $this->assertSame('123-123-123', $result->getResourceId());
        $this->assertSame('en', $result->getLocale());

        $this->assertSame($contentView->getType(), $result->getType());
        $this->assertSame($contentView->getData(), $result->getData());
    }
}
