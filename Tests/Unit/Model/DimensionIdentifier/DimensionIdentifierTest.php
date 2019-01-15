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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\DimensionIdentifier;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifier;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierAttributeInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\Exception\DimensionIdentifierAttributeNotFoundException;

class DimensionIdentifierTest extends TestCase
{
    public function testGetId(): void
    {
        $dimensionIdentifier = new DimensionIdentifier('123-123-123');

        $this->assertEquals('123-123-123', $dimensionIdentifier->getId());
    }

    public function testGetAttributeCount(): void
    {
        $dimensionIdentifier = new DimensionIdentifier('123-123-123');

        $this->assertEquals(0, $dimensionIdentifier->getAttributeCount());
    }

    public function testGetAttributes(): void
    {
        $attribute = $this->prophesize(DimensionIdentifierAttributeInterface::class);
        $attribute->setDimensionIdentifier(
            Argument::that(
                function (DimensionIdentifier $dimensionIdentifier) {
                    return '123-123-123' === $dimensionIdentifier->getId();
                }
            )
        )->shouldBeCalled()->willReturn($attribute->reveal());

        $dimensionIdentifier = new DimensionIdentifier('123-123-123', [$attribute->reveal()]);

        $this->assertEquals(1, $dimensionIdentifier->getAttributeCount());
        $this->assertEquals([$attribute->reveal()], $dimensionIdentifier->getAttributes());
    }

    public function testGetAttributeValue(): void
    {
        $attribute = $this->prophesize(DimensionIdentifierAttributeInterface::class);
        $attribute->setDimensionIdentifier(Argument::any())->willReturn($attribute->reveal());
        $attribute->getKey()->willReturn(DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE);
        $attribute->getValue()->willReturn(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE);

        $dimensionIdentifier = new DimensionIdentifier('123-123-123', [$attribute->reveal()]);

        $this->assertEquals('live', $dimensionIdentifier->getAttributeValue(DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE));
    }

    public function testGetAttributeValueNotFound(): void
    {
        $this->expectException(DimensionIdentifierAttributeNotFoundException::class);

        $attribute = $this->prophesize(DimensionIdentifierAttributeInterface::class);
        $attribute->setDimensionIdentifier(Argument::any())->willReturn($attribute->reveal());
        $attribute->getKey()->willReturn(DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE);
        $attribute->getValue()->willReturn(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE);

        $dimensionIdentifier = new DimensionIdentifier('123-123-123', [$attribute->reveal()]);

        $dimensionIdentifier->getAttributeValue(DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE);
    }
}
