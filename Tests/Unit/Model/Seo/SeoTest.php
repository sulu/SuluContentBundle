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
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimension;

class SeoTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($dimension->reveal(), $seoDimension->getDimension());
    }

    public function testGetResourceKey(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals(self::RESOURCE_KEY, $seoDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals('seo1', $seoDimension->getResourceId());
    }

    public function testGetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getTitle());
    }

    public function testSetTitle(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setTitle('title-1'));
        $this->assertEquals('title-1', $seoDimension->getTitle());
    }

    public function testGetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getDescription());
    }

    public function testSetDescription(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setDescription('description-1'));
        $this->assertEquals('description-1', $seoDimension->getDescription());
    }

    public function testGetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getKeywords());
    }

    public function testSetKeywords(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setKeywords('keywords-1'));
        $this->assertEquals('keywords-1', $seoDimension->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getCanonicalUrl());
    }

    public function testSetCanonicalUrl(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setCanonicalUrl('url-1'));
        $this->assertEquals('url-1', $seoDimension->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getNoIndex());
    }

    public function testSetNoIndex(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setNoIndex(false));
        $this->assertFalse($seoDimension->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getNoFollow());
    }

    public function testSetNoFollow(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setNoFollow(true));
        $this->assertTrue($seoDimension->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertNull($seoDimension->getHideInSitemap());
    }

    public function testSetHideInSitemap(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $this->assertEquals($seoDimension, $seoDimension->setHideInSitemap(false));
        $this->assertFalse($seoDimension->getHideInSitemap());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $seoDimension = new SeoDimension($dimension->reveal(), self::RESOURCE_KEY, 'seo1');

        $otherDimension = $this->prophesize(DimensionInterface::class);
        $otherSeo = new SeoDimension(
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

        $this->assertEquals($seoDimension, $seoDimension->copyAttributesFrom($otherSeo));

        $this->assertEquals($dimension->reveal(), $seoDimension->getDimension());
        $this->assertEquals(self::RESOURCE_KEY, $seoDimension->getResourceKey());
        $this->assertEquals('seo1', $seoDimension->getResourceId());
        $this->assertEquals('other-title', $seoDimension->getTitle());
        $this->assertEquals('other-description', $seoDimension->getDescription());
        $this->assertNull($seoDimension->getKeywords());
        $this->assertEquals('other-url', $seoDimension->getCanonicalUrl());
        $this->assertFalse($seoDimension->getNoIndex());
        $this->assertTrue($seoDimension->getNoFollow());
        $this->assertNull($seoDimension->getHideInSitemap());
    }
}
