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
use Sulu\Bundle\ContentBundle\Model\Content\Message\RemoveContentMessage;

class RemoveContentMessageTest extends TestCase
{
    const RESOURCE_KEY = 'contents';

    public function testGetResourceKey(): void
    {
        $message = new RemoveContentMessage(self::RESOURCE_KEY, 'product-1');

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new RemoveContentMessage(self::RESOURCE_KEY, 'product-1');

        $this->assertEquals('product-1', $message->getResourceId());
    }
}
