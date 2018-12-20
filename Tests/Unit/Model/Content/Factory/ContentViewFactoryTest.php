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
use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactory;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class ContentViewFactoryTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testCreate(): void
    {
        $factory = new ContentViewFactory();

        $dimension = $this->prophesize(DimensionInterface::class);

        $contentDimension1 = $this->prophesize(ContentInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension->reveal());
        $contentDimension1->getResourceKey()->willReturn(self::RESOURCE_KEY);
        $contentDimension1->getResourceId()->willReturn('product-1');
        $contentDimension1->getType()->willReturn('default');
        $contentDimension1->getData()->willReturn(['title' => 'Sulu']);

        $contentDimension2 = $this->prophesize(ContentInterface::class);
        $contentDimension2->getData()->willReturn(['article' => '<p>Sulu is awesome</p>']);

        $result = $factory->create([$contentDimension1->reveal(), $contentDimension2->reveal()], 'en');

        if (!$result) {
            $this->fail('Result is null');

            return;
        }

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
