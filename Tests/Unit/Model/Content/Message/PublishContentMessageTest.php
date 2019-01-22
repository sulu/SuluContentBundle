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
    const RESOURCE_KEY = 'test_resource_contents';

    public function testGetResourceKey(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame('resource-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame('en', $message->getLocale());
    }

    public function testGetContent(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertNull($message->getContent());
    }

    public function testSetContent(): void
    {
        $message = new PublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $content = $this->prophesize(ContentViewInterface::class);

        $this->assertSame($message, $message->setContent($content->reveal()));
        $this->assertSame($content->reveal(), $message->getContent());
    }
}
