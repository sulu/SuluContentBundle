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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\Content;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Seo;

class SeoTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($dimension->reveal(), $content->getDimension());
    }

    public function testGetResourceKey(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals(self::RESOURCE_KEY, $content->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals('seo1', $content->getResourceId());
    }

    public function testGetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getTitle());
    }

    public function testSetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setTitle('title-1'));
        $this->assertEquals('title-1', $content->getTitle());
    }

    public function testGetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getDescription());
    }

    public function testSetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setDescription('description-1'));
        $this->assertEquals('description-1', $content->getDescription());
    }

    public function testGetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getKeywords());
    }

    public function testSetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setKeywords('keywords-1'));
        $this->assertEquals('keywords-1', $content->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getCanonicalUrl());
    }

    public function testSetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setCanonicalUrl('url-1'));
        $this->assertEquals('url-1', $content->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getNoIndex());
    }

    public function testSetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setNoIndex(false));
        $this->assertFalse($content->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getNoFollow());
    }

    public function testSetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setNoFollow(true));
        $this->assertTrue($content->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($content->getHideInSitemap());
    }

    public function testSetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($content, $content->setHideInSitemap(false));
        $this->assertFalse($content->getHideInSitemap());
    }
}
