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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierAttribute;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

class DimensionIdentifierAttributeTest extends TestCase
{
    public function testGetDimension(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $attribute = new DimensionIdentifierAttribute(
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE,
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT
        );
        $this->assertSame($attribute, $attribute->setDimensionIdentifier($dimensionIdentifier->reveal()));

        $this->assertSame($dimensionIdentifier->reveal(), $attribute->getDimensionIdentifier());
    }

    public function testGetKey(): void
    {
        $attribute = new DimensionIdentifierAttribute(
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE,
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT
        );

        $this->assertSame(DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE, $attribute->getKey());
    }

    public function testGetValue(): void
    {
        $attribute = new DimensionIdentifierAttribute(
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE,
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT
        );

        $this->assertSame(DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT, $attribute->getValue());
    }
}
