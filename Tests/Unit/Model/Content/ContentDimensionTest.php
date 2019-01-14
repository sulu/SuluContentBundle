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
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimension;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class ContentDimensionTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals($dimension->reveal(), $contentDimension->getDimension());
    }

    public function testGetResourceKey(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals(self::RESOURCE_KEY, $contentDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals('product-1', $contentDimension->getResourceId());
    }

    public function testGetType(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $this->assertEquals('default', $contentDimension->getType());
    }

    public function testGetData(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension(
            $dimension->reveal(),
            self::RESOURCE_KEY,
            'product-1',
            'default',
            ['title' => 'Sulu is awesome']
        );

        $this->assertEquals(['title' => 'Sulu is awesome'], $contentDimension->getData());
    }

    public function testSetType(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $this->assertEquals($contentDimension, $contentDimension->setType('homepage'));
        $this->assertEquals('homepage', $contentDimension->getType());
    }

    public function testSetData(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension(
            $dimension->reveal(),
            self::RESOURCE_KEY,
            'product-1',
            'default',
            ['title' => 'Sulu is great']
        );

        $this->assertEquals($contentDimension, $contentDimension->setData(['title' => 'Sulu is awesome']));
        $this->assertEquals(['title' => 'Sulu is awesome'], $contentDimension->getData());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $contentDimension = new ContentDimension($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $otherDimension = $this->prophesize(DimensionInterface::class);
        $otherContent = new ContentDimension(
            $otherDimension->reveal(),
            'other-resource-key',
            'other-resource-id',
            'other-type',
            ['title' => 'other-title']
        );

        $this->assertEquals($contentDimension, $contentDimension->copyAttributesFrom($otherContent));

        $this->assertEquals($dimension->reveal(), $contentDimension->getDimension());
        $this->assertEquals(self::RESOURCE_KEY, $contentDimension->getResourceKey());
        $this->assertEquals('product-1', $contentDimension->getResourceId());
        $this->assertEquals('other-type', $contentDimension->getType());
        $this->assertEquals(['title' => 'other-title'], $contentDimension->getData());
    }
}
