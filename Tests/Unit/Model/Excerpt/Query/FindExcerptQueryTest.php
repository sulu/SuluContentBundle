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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\Query;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Query\FindExcerptQuery;

class FindExcerptQueryTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testGetResourceKey(): void
    {
        $query = new FindExcerptQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $query->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $query = new FindExcerptQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('resource-1', $query->getResourceId());
    }

    public function testGetLocale(): void
    {
        $query = new FindExcerptQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('en', $query->getLocale());
    }

    public function testGetExcerpt(): void
    {
        $this->expectException(MissingResultException::class);

        $query = new FindExcerptQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $query->getExcerpt();
    }

    public function testSetExcerpt(): void
    {
        $query = new FindExcerptQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $excerpt = $this->prophesize(ExcerptViewInterface::class);

        $this->assertEquals($query, $query->setExcerpt($excerpt->reveal()));
        $this->assertEquals($excerpt->reveal(), $query->getExcerpt());
    }
}
