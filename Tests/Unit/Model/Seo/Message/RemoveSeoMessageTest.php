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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\RemoveSeoMessage;

class RemoveSeoMessageTest extends TestCase
{
    const RESOURCE_KEY = 'seos';

    public function testGetResourceKey(): void
    {
        $message = new RemoveSeoMessage(self::RESOURCE_KEY, 'product-1');

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new RemoveSeoMessage(self::RESOURCE_KEY, 'product-1');

        $this->assertEquals('product-1', $message->getResourceId());
    }
}
