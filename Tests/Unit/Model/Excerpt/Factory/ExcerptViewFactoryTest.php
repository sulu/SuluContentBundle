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
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptViewFactoryTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testCreate(): void
    {
        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->shouldBeCalled()->willReturn(2);

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->shouldBeCalled()->willReturn(1);
        $tag1->getName()->shouldBeCalled()->willReturn('tag-1');
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $media1 = $this->prophesize(MediaInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getOrder()->shouldBeCalled()->willReturn(1);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getOrder()->shouldBeCalled()->willReturn(3);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());

        $media2 = $this->prophesize(MediaInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);
        $iconReference2 = $this->prophesize(IconReferenceInterface::class);
        $iconReference2->getOrder()->shouldBeCalled()->willReturn(2);
        $iconReference2->getMedia()->shouldBeCalled()->willReturn($media2->reveal());

        $media3 = $this->prophesize(MediaInterface::class);
        $media3->getId()->shouldBeCalled()->willReturn(3);
        $iconReference3 = $this->prophesize(IconReferenceInterface::class);
        $iconReference3->getOrder()->shouldBeCalled()->willReturn(3);
        $iconReference3->getMedia()->shouldBeCalled()->willReturn($media3->reveal());
        $imageReference3 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference3->getOrder()->shouldBeCalled()->willReturn(1);
        $imageReference3->getMedia()->shouldBeCalled()->willReturn($media3->reveal());

        $factory = new ExcerptViewFactory();

        $excerptDimension1 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension1->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $excerptDimension1->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $excerptDimension1->getTitle()->shouldBeCalled()->willReturn('title-1');
        $excerptDimension1->getMore()->shouldBeCalled()->willReturn('more-1');
        $excerptDimension1->getDescription()->shouldBeCalled()->willReturn('description-1');
        $excerptDimension1->getCategories()->shouldBeCalled()->willReturn([$category1->reveal()]);
        $excerptDimension1->getTags()->shouldBeCalled()->willReturn([$tagReference1->reveal()]);
        $excerptDimension1->getIcons()->shouldBeCalled()->willReturn([$iconReference1->reveal(), $iconReference2->reveal()]);
        $excerptDimension1->getImages()->shouldBeCalled()->willReturn([$imageReference1->reveal()]);

        $excerptDimension2 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension2->getResourceKey()->shouldNotBeCalled();
        $excerptDimension2->getResourceId()->shouldNotBeCalled();
        $excerptDimension2->getTitle()->shouldBeCalled()->willReturn(null);
        $excerptDimension2->getMore()->shouldBeCalled()->willReturn('more-2');
        $excerptDimension2->getDescription()->shouldBeCalled()->willReturn(null);
        $excerptDimension2->getCategories()->shouldBeCalled()->willReturn([$category2->reveal()]);
        $excerptDimension2->getTags()->shouldBeCalled()->willReturn([$tagReference1->reveal()]);
        $excerptDimension2->getIcons()->shouldBeCalled()->willReturn([$iconReference3->reveal()]);
        $excerptDimension2->getImages()->shouldBeCalled()->willReturn([$imageReference3->reveal(), $imageReference1->reveal()]);

        $result = $factory->create([$excerptDimension1->reveal(), $excerptDimension2->reveal()], 'en');

        $this->assertNotNull($result);
        $this->assertSame(self::RESOURCE_KEY, $result->getResourceKey());
        $this->assertSame('resource-1', $result->getResourceId());
        $this->assertSame('title-1', $result->getTitle());
        $this->assertSame('more-2', $result->getMore());
        $this->assertSame('description-1', $result->getDescription());
        $this->assertSame([1, 2], $result->getCategoryIds());
        $this->assertSame(['tag-1'], $result->getTagNames());
        $this->assertSame(['ids' => [1, 2, 3]], $result->getIconsData());
        $this->assertSame(['ids' => [3, 1]], $result->getImagesData());
    }

    public function testCreateNull(): void
    {
        $factory = new ExcerptViewFactory();

        $this->assertNull($factory->create([], 'en'));
    }
}
