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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\Factory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactory;

class ContentViewFactoryTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testCreate(): void
    {
        $factory = new ContentViewFactory();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $contentDimension1->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $contentDimension1->getType()->shouldBeCalled()->willReturn('default');
        $contentDimension1->getData()->shouldBeCalled()->willReturn(['title' => 'Sulu']);

        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getData()->shouldBeCalled()->willReturn(['article' => '<p>Sulu is awesome</p>']);

        $result = $factory->create([$contentDimension1->reveal(), $contentDimension2->reveal()], 'en');

        $this->assertNotNull($result);
        $this->assertEquals(self::RESOURCE_KEY, $result->getResourceKey());
        $this->assertEquals('product-1', $result->getResourceId());
        $this->assertEquals('default', $result->getType());
        $this->assertEquals(['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>'], $result->getData());
        $this->assertEquals('en', $result->getLocale());
    }

    public function testCreateNull(): void
    {
        $factory = new ContentViewFactory();

        $this->assertNull($factory->create([], 'en'));
    }
}
