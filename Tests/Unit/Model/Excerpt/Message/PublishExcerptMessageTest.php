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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\PublishExcerptMessage;

class PublishExcerptMessageTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testGetResourceKey(): void
    {
        $message = new PublishExcerptMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new PublishExcerptMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('resource-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new PublishExcerptMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('en', $message->getLocale());
    }

    public function testGetExcerpt(): void
    {
        $message = new PublishExcerptMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertNull($message->getExcerpt());
    }

    public function testSetExcerpt(): void
    {
        $message = new PublishExcerptMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $content = $this->prophesize(ExcerptViewInterface::class);

        $this->assertEquals($message, $message->setExcerpt($content->reveal()));
        $this->assertEquals($content->reveal(), $message->getExcerpt());
    }
}
