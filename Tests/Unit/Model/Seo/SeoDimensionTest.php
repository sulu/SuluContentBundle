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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimension;

class SeoDimensionTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testCreateClone(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $newSeoDimension = $seoDimension->createClone('new-resource-1');

        $this->assertSame('new-resource-1', $newSeoDimension->getResourceId());
    }

    public function testGetDimension(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($dimensionIdentifier->reveal(), $seoDimension->getDimensionIdentifier());
    }

    public function testGetResourceKey(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame(self::RESOURCE_KEY, $seoDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame('resource-1', $seoDimension->getResourceId());
    }

    public function testGetTitle(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getTitle());
    }

    public function testSetTitle(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setTitle('title-1'));
        $this->assertSame('title-1', $seoDimension->getTitle());
    }

    public function testGetDescription(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getDescription());
    }

    public function testSetDescription(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setDescription('description-1'));
        $this->assertSame('description-1', $seoDimension->getDescription());
    }

    public function testGetKeywords(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getKeywords());
    }

    public function testSetKeywords(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setKeywords('keywords-1'));
        $this->assertSame('keywords-1', $seoDimension->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getCanonicalUrl());
    }

    public function testSetCanonicalUrl(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setCanonicalUrl('url-1'));
        $this->assertSame('url-1', $seoDimension->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getNoIndex());
    }

    public function testSetNoIndex(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setNoIndex(false));
        $this->assertFalse($seoDimension->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getNoFollow());
    }

    public function testSetNoFollow(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setNoFollow(true));
        $this->assertTrue($seoDimension->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($seoDimension->getHideInSitemap());
    }

    public function testSetHideInSitemap(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($seoDimension, $seoDimension->setHideInSitemap(false));
        $this->assertFalse($seoDimension->getHideInSitemap());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $seoDimension = new SeoDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $otherDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $otherSeo = new SeoDimension(
            $otherDimensionIdentifier->reveal(),
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

        $this->assertSame($seoDimension, $seoDimension->copyAttributesFrom($otherSeo));

        $this->assertSame($dimensionIdentifier->reveal(), $seoDimension->getDimensionIdentifier());
        $this->assertSame(self::RESOURCE_KEY, $seoDimension->getResourceKey());
        $this->assertSame('resource-1', $seoDimension->getResourceId());
        $this->assertSame('other-title', $seoDimension->getTitle());
        $this->assertSame('other-description', $seoDimension->getDescription());
        $this->assertNull($seoDimension->getKeywords());
        $this->assertSame('other-url', $seoDimension->getCanonicalUrl());
        $this->assertFalse($seoDimension->getNoIndex());
        $this->assertTrue($seoDimension->getNoFollow());
        $this->assertNull($seoDimension->getHideInSitemap());
    }
}
