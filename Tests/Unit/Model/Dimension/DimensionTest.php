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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Dimension;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Model\Dimension\Dimension;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionAttributeInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\Exception\DimensionAttributeNotFoundException;

class DimensionTest extends TestCase
{
    public function testGetId(): void
    {
        $dimension = new Dimension('123-123-123');

        $this->assertEquals('123-123-123', $dimension->getId());
    }

    public function testGetAttributeCount(): void
    {
        $dimension = new Dimension('123-123-123');

        $this->assertEquals(0, $dimension->getAttributeCount());
    }

    public function testGetAttributes(): void
    {
        $attribute = $this->prophesize(DimensionAttributeInterface::class);
        $attribute->setDimension(
            Argument::that(
                function (Dimension $dimension) {
                    return '123-123-123' === $dimension->getId();
                }
            )
        )->shouldBeCalled()->willReturn($attribute->reveal());

        $dimension = new Dimension('123-123-123', [$attribute->reveal()]);

        $this->assertEquals(1, $dimension->getAttributeCount());
        $this->assertEquals([$attribute->reveal()], $dimension->getAttributes());
    }

    public function testGetAttributeValue(): void
    {
        $attribute = $this->prophesize(DimensionAttributeInterface::class);
        $attribute->setDimension(Argument::any())->willReturn($attribute->reveal());
        $attribute->getKey()->willReturn(DimensionInterface::ATTRIBUTE_KEY_STAGE);
        $attribute->getValue()->willReturn(DimensionInterface::ATTRIBUTE_VALUE_LIVE);

        $dimension = new Dimension('123-123-123', [$attribute->reveal()]);

        $this->assertEquals('live', $dimension->getAttributeValue(DimensionInterface::ATTRIBUTE_KEY_STAGE));
    }

    public function testGetAttributeValueNotFound(): void
    {
        $this->expectException(DimensionAttributeNotFoundException::class);

        $attribute = $this->prophesize(DimensionAttributeInterface::class);
        $attribute->setDimension(Argument::any())->willReturn($attribute->reveal());
        $attribute->getKey()->willReturn(DimensionInterface::ATTRIBUTE_KEY_STAGE);
        $attribute->getValue()->willReturn(DimensionInterface::ATTRIBUTE_VALUE_LIVE);

        $dimension = new Dimension('123-123-123', [$attribute->reveal()]);

        $dimension->getAttributeValue(DimensionInterface::ATTRIBUTE_KEY_LOCALE);
    }
}
