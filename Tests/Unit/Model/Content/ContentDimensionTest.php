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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

class ContentDimensionTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testGetDimension(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($dimensionIdentifier->reveal(), $contentDimension->getDimensionIdentifier());
    }

    public function testGetResourceKey(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame(self::RESOURCE_KEY, $contentDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame('resource-1', $contentDimension->getResourceId());
    }

    public function testGetType(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1', 'default');

        $this->assertSame('default', $contentDimension->getType());
    }

    public function testGetData(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension(
            $dimensionIdentifier->reveal(),
            self::RESOURCE_KEY,
            'resource-1',
            'default',
            ['title' => 'Sulu is awesome']
        );

        $this->assertSame(['title' => 'Sulu is awesome'], $contentDimension->getData());
    }

    public function testSetType(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1', 'default');

        $this->assertSame($contentDimension, $contentDimension->setType('homepage'));
        $this->assertSame('homepage', $contentDimension->getType());
    }

    public function testSetData(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension(
            $dimensionIdentifier->reveal(),
            self::RESOURCE_KEY,
            'resource-1',
            'default',
            ['title' => 'Sulu is great']
        );

        $this->assertSame($contentDimension, $contentDimension->setData(['title' => 'Sulu is awesome']));
        $this->assertSame(['title' => 'Sulu is awesome'], $contentDimension->getData());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $contentDimension = new ContentDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1', 'default');

        $otherDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $otherContent = new ContentDimension(
            $otherDimensionIdentifier->reveal(),
            'other-resource-key',
            'other-resource-id',
            'other-type',
            ['title' => 'other-title']
        );

        $this->assertSame($contentDimension, $contentDimension->copyAttributesFrom($otherContent));

        $this->assertSame($dimensionIdentifier->reveal(), $contentDimension->getDimensionIdentifier());
        $this->assertSame(self::RESOURCE_KEY, $contentDimension->getResourceKey());
        $this->assertSame('resource-1', $contentDimension->getResourceId());
        $this->assertSame('other-type', $contentDimension->getType());
        $this->assertSame(['title' => 'other-title'], $contentDimension->getData());
    }
}
