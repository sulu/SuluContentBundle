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
            ['key-1' => 'value-1']
        );

        $this->assertEquals(self::RESOURCE_KEY, $contentView->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['key-1' => 'value-1']
        );

        $this->assertEquals('resource-1', $contentView->getResourceId());
    }

    public function testGetLocale(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['key-1' => 'value-1']
        );

        $this->assertEquals('en', $contentView->getLocale());
    }

    public function testGetType(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['key-1' => 'value-1']
        );

        $this->assertEquals('type-1', $contentView->getType());
    }

    public function testGetData(): void
    {
        $contentView = new ContentView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'type-1',
            ['key-1' => 'value-1']
        );

        $this->assertEquals(['key-1' => 'value-1'], $contentView->getData());
    }
}
