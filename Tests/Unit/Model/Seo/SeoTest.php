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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Seo;

class SeoTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($dimension->reveal(), $seo->getDimension());
    }

    public function testGetResourceKey(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals(self::RESOURCE_KEY, $seo->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals('seo1', $seo->getResourceId());
    }

    public function testGetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getTitle());
    }

    public function testSetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setTitle('title-1'));
        $this->assertEquals('title-1', $seo->getTitle());
    }

    public function testGetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getDescription());
    }

    public function testSetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setDescription('description-1'));
        $this->assertEquals('description-1', $seo->getDescription());
    }

    public function testGetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getKeywords());
    }

    public function testSetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setKeywords('keywords-1'));
        $this->assertEquals('keywords-1', $seo->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getCanonicalUrl());
    }

    public function testSetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setCanonicalUrl('url-1'));
        $this->assertEquals('url-1', $seo->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getNoIndex());
    }

    public function testSetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setNoIndex(false));
        $this->assertFalse($seo->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getNoFollow());
    }

    public function testSetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setNoFollow(true));
        $this->assertTrue($seo->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seo->getHideInSitemap());
    }

    public function testSetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seo, $seo->setHideInSitemap(false));
        $this->assertFalse($seo->getHideInSitemap());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seo = new Seo($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $otherDimension = $this->prophesize(DimensionInterface::class);
        $otherSeo = new Seo(
            $otherDimension->reveal(),
            'other-resource-key',
            'other-resource-id',
            'other-title',
            'other-description',
            null,
            'other-url',
            false,
            true,
            null
        );

        $this->assertEquals($seo, $seo->copyAttributesFrom($otherSeo));

        $this->assertEquals($dimension->reveal(), $seo->getDimension());
        $this->assertEquals(self::RESOURCE_KEY, $seo->getResourceKey());
        $this->assertEquals('seo1', $seo->getResourceId());
        $this->assertEquals('other-title', $seo->getTitle());
        $this->assertEquals('other-description', $seo->getDescription());
        $this->assertNull($seo->getKeywords());
        $this->assertEquals('other-url', $seo->getCanonicalUrl());
        $this->assertFalse($seo->getNoIndex());
        $this->assertTrue($seo->getNoFollow());
        $this->assertNull($seo->getHideInSitemap());
    }
}
