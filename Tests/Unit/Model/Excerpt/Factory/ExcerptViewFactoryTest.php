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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\Factory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactory;

class ExcerptViewFactoryTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testCreate(): void
    {
        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->shouldBeCalled()->willReturn(2);

        $media1 = $this->prophesize(CategoryInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);

        $media2 = $this->prophesize(CategoryInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);

        $media3 = $this->prophesize(CategoryInterface::class);
        $media3->getId()->shouldBeCalled()->willReturn(3);

        $media4 = $this->prophesize(CategoryInterface::class);
        $media4->getId()->shouldBeCalled()->willReturn(4);

        $factory = new ExcerptViewFactory();

        $excerptDimension1 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension1->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $excerptDimension1->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $excerptDimension1->getTitle()->shouldBeCalled()->willReturn('title-1');
        $excerptDimension1->getMore()->shouldBeCalled()->willReturn('more-1');
        $excerptDimension1->getDescription()->shouldBeCalled()->willReturn('description-1');
        $excerptDimension1->getCategories()->shouldBeCalled()->willReturn([$category1->reveal()]);
        $excerptDimension1->getTagReferences()->shouldBeCalled()->willReturn([]);
        $excerptDimension1->getIcons()->shouldBeCalled()->willReturn([$media1->reveal(), $media2->reveal()]);
        $excerptDimension1->getImages()->shouldBeCalled()->willReturn([]);

        $excerptDimension2 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension2->getResourceKey()->shouldNotBeCalled();
        $excerptDimension2->getResourceId()->shouldNotBeCalled();
        $excerptDimension2->getTitle()->shouldBeCalled()->willReturn(null);
        $excerptDimension2->getMore()->shouldBeCalled()->willReturn('more-2');
        $excerptDimension2->getDescription()->shouldBeCalled()->willReturn(null);
        $excerptDimension2->getCategories()->shouldBeCalled()->willReturn([$category2->reveal()]);
        $excerptDimension2->getTagReferences()->shouldBeCalled()->willReturn([]);
        $excerptDimension2->getIcons()->shouldBeCalled()->willReturn([$media3->reveal()]);
        $excerptDimension2->getImages()->shouldBeCalled()->willReturn([$media4->reveal()]);

        $result = $factory->create([$excerptDimension1->reveal(), $excerptDimension2->reveal()], 'en');

        $this->assertNotNull($result);
        $this->assertEquals(self::RESOURCE_KEY, $result->getResourceKey());
        $this->assertEquals('resource-1', $result->getResourceId());
        $this->assertEquals('title-1', $result->getTitle());
        $this->assertEquals('more-2', $result->getMore());
        $this->assertEquals('description-1', $result->getDescription());
        $this->assertEquals([1, 2], $result->getCategoryIds());
        $this->assertEquals([], $result->getTagNames());
        $this->assertEquals(['ids' => [1, 2, 3]], $result->getIconsData());
        $this->assertEquals(['ids' => [4]], $result->getImagesData());
    }

    public function testCreateNull(): void
    {
        $factory = new ExcerptViewFactory();

        $this->assertNull($factory->create([], 'en'));
    }
}
