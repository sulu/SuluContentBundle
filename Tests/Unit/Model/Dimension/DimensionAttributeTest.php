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
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionAttribute;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class DimensionAttributeTest extends TestCase
{
    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);

        $attribute = new DimensionAttribute(
            DimensionInterface::ATTRIBUTE_KEY_STAGE,
            DimensionInterface::ATTRIBUTE_VALUE_DRAFT
        );
        $this->assertEquals($attribute, $attribute->setDimension($dimension->reveal()));

        $this->assertEquals($dimension->reveal(), $attribute->getDimension());
    }

    public function testGetKey(): void
    {
        $attribute = new DimensionAttribute(
            DimensionInterface::ATTRIBUTE_KEY_STAGE,
            DimensionInterface::ATTRIBUTE_VALUE_DRAFT
        );

        $this->assertEquals(DimensionInterface::ATTRIBUTE_KEY_STAGE, $attribute->getKey());
    }

    public function testGetValue(): void
    {
        $attribute = new DimensionAttribute(
            DimensionInterface::ATTRIBUTE_KEY_STAGE,
            DimensionInterface::ATTRIBUTE_VALUE_DRAFT
        );

        $this->assertEquals(DimensionInterface::ATTRIBUTE_VALUE_DRAFT, $attribute->getValue());
    }
}
