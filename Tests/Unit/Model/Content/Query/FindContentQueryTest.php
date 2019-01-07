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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\Query;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Query\FindContentQuery;

class FindContentQueryTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testGetResourceKey(): void
    {
        $query = new FindContentQuery(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $query->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $query = new FindContentQuery(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals('product-1', $query->getResourceId());
    }

    public function testGetLocale(): void
    {
        $query = new FindContentQuery(self::RESOURCE_KEY, 'product-1', 'en');

        $this->assertEquals('en', $query->getLocale());
    }

    public function testGetContent(): void
    {
        $this->expectException(MissingResultException::class);

        $query = new FindContentQuery(self::RESOURCE_KEY, 'product-1', 'en');

        $query->getContent();
    }

    public function testSetContent(): void
    {
        $query = new FindContentQuery(self::RESOURCE_KEY, 'product-1', 'en');

        $content = $this->prophesize(ContentViewInterface::class);

        $this->assertEquals($query, $query->setContent($content->reveal()));
        $this->assertEquals($content->reveal(), $query->getContent());
    }
}
