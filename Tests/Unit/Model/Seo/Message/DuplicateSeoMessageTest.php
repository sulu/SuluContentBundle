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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\DuplicateSeoMessage;

class DuplicateSeoMessageTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testGetResourceKey(): void
    {
        $message = new DuplicateSeoMessage(self::RESOURCE_KEY, 'resource-1', 'new-resource-1');

        $this->assertSame(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new DuplicateSeoMessage(self::RESOURCE_KEY, 'resource-1', 'new-resource-1');

        $this->assertSame('resource-1', $message->getResourceId());
    }

    public function testGetNewResourceId(): void
    {
        $message = new DuplicateSeoMessage(self::RESOURCE_KEY, 'resource-1', 'new-resource-1');

        $this->assertSame('resource-1', $message->getNewResourceId());
    }
}
