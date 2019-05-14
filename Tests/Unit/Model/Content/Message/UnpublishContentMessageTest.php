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
use Sulu\Bundle\ContentBundle\Model\Content\Message\UnpublishContentMessage;

class UnpublishContentMessageTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testGetResourceKey(): void
    {
        $message = new UnpublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new UnpublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame('resource-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new UnpublishContentMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertSame('en', $message->getLocale());
    }
}
