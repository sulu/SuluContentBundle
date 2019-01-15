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

class ContentTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals($dimension->reveal(), $content->getDimension());
    }

    public function testGetResourceKey(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals(self::RESOURCE_KEY, $content->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1');

        $this->assertEquals('product-1', $content->getResourceId());
    }

    public function testGetType(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $this->assertEquals('default', $content->getType());
    }

    public function testGetData(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content(
            $dimension->reveal(),
            self::RESOURCE_KEY,
            'product-1',
            'default',
            ['title' => 'Sulu is awesome']
        );

        $this->assertEquals(['title' => 'Sulu is awesome'], $content->getData());
    }

    public function testSetType(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $this->assertEquals($content, $content->setType('homepage'));
        $this->assertEquals('homepage', $content->getType());
    }

    public function testSetData(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content(
            $dimension->reveal(),
            self::RESOURCE_KEY,
            'product-1',
            'default',
            ['title' => 'Sulu is great']
        );

        $this->assertEquals($content, $content->setData(['title' => 'Sulu is awesome']));
        $this->assertEquals(['title' => 'Sulu is awesome'], $content->getData());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $content = new Content($dimension->reveal(), self::RESOURCE_KEY, 'product-1', 'default');

        $otherDimension = $this->prophesize(DimensionInterface::class);
        $otherContent = new Content(
            $otherDimension->reveal(),
            'other-resource-key',
            'other-resource-id',
            'other-type',
            ['title' => 'other-title']
        );

        $this->assertEquals($content, $content->copyAttributesFrom($otherContent));

        $this->assertEquals($dimension->reveal(), $content->getDimension());
        $this->assertEquals(self::RESOURCE_KEY, $content->getResourceKey());
        $this->assertEquals('product-1', $content->getResourceId());
        $this->assertEquals('other-type', $content->getType());
        $this->assertEquals(['title' => 'other-title'], $content->getData());
    }
}
