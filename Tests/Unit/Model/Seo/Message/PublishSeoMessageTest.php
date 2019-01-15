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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\PublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class PublishSeoMessageTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testGetResourceKey(): void
    {
        $message = new PublishSeoMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new PublishSeoMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('resource-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new PublishSeoMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('en', $message->getLocale());
    }

    public function testGetSeo(): void
    {
        $message = new PublishSeoMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertNull($message->getSeo());
    }

    public function testSetSeo(): void
    {
        $message = new PublishSeoMessage(self::RESOURCE_KEY, 'resource-1', 'en');

        $content = $this->prophesize(SeoViewInterface::class);

        $this->assertEquals($message, $message->setSeo($content->reveal()));
        $this->assertEquals($content->reveal(), $message->getSeo());
    }
}
