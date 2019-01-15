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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\Query;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class FindSeoQueryTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testGetResourceKey(): void
    {
        $query = new FindSeoQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals(self::RESOURCE_KEY, $query->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $query = new FindSeoQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('resource-1', $query->getResourceId());
    }

    public function testGetLocale(): void
    {
        $query = new FindSeoQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $this->assertEquals('en', $query->getLocale());
    }

    public function testGetSeo(): void
    {
        $this->expectException(MissingResultException::class);

        $query = new FindSeoQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $query->getSeo();
    }

    public function testSetSeo(): void
    {
        $query = new FindSeoQuery(self::RESOURCE_KEY, 'resource-1', 'en');

        $seo = $this->prophesize(SeoViewInterface::class);

        $this->assertEquals($query, $query->setSeo($seo->reveal()));
        $this->assertEquals($seo->reveal(), $query->getSeo());
    }
}
