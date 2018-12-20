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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;

class PublishContentMessageTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testGetResourceKey(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals('product-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals('en', $message->getLocale());
    }

    public function testGetContent(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertNull($message->getContent());
    }

    public function testSetContent(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'product-1', 'en');

        $content = $this->prophesize(ContentViewInterface::class);

        $this->assertEquals($message, $message->setContent($content->reveal()));
        $this->assertEquals($content->reveal(), $message->getContent());
    }
}
