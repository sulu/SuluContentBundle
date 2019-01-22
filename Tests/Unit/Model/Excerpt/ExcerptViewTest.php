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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptView;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptViewTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testGetResourceKey(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertSame(self::RESOURCE_KEY, $excerptView->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertSame('resource-1', $excerptView->getResourceId());
    }

    public function testGetLocale(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertSame('en', $excerptView->getLocale());
    }

    public function testGetTitle(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertSame('title-1', $excerptView->getTitle());
    }

    public function testGetMore(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertSame('more-1', $excerptView->getMore());
    }

    public function testGetDescription(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null
        );

        $this->assertNull($excerptView->getDescription());
    }

    public function testGetCategoryIds(): void
    {
        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldbeCalled()->willReturn(1);

        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->shouldbeCalled()->willReturn(2);

        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$category1->reveal(), $category2->reveal()]
        );

        $this->assertSame([1, 2], $excerptView->getCategoryIds());
    }

    public function testGetTagNames(): void
    {
        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getName()->shouldBeCalled()->willReturn('tag-1');
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getOrder()->shouldBeCalled()->willReturn(3);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getName()->shouldBeCalled()->willReturn('tag-2');
        $tagReference2 = $this->prophesize(TagReferenceInterface::class);
        $tagReference2->getOrder()->shouldBeCalled()->willReturn(1);
        $tagReference2->getTag()->shouldBeCalled()->willReturn($tag2->reveal());

        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [],
            [$tagReference1->reveal(), $tagReference2->reveal()]
        );

        $this->assertSame(['tag-2', 'tag-1'], $excerptView->getTagNames());
    }

    public function testGetIconsData(): void
    {
        $media1 = $this->prophesize(MediaInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getOrder()->shouldBeCalled()->willReturn(3);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());

        $media2 = $this->prophesize(MediaInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);
        $iconReference2 = $this->prophesize(IconReferenceInterface::class);
        $iconReference2->getOrder()->shouldBeCalled()->willReturn(1);
        $iconReference2->getMedia()->shouldBeCalled()->willReturn($media2->reveal());

        $media3 = $this->prophesize(MediaInterface::class);
        $media3->getId()->shouldBeCalled()->willReturn(3);
        $iconReference3 = $this->prophesize(IconReferenceInterface::class);
        $iconReference3->getOrder()->shouldBeCalled()->willReturn(2);
        $iconReference3->getMedia()->shouldBeCalled()->willReturn($media3->reveal());

        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [],
            [],
            [$iconReference2->reveal(), $iconReference1->reveal(), $iconReference3->reveal()]
        );

        $this->assertSame(['ids' => [2, 3, 1]], $excerptView->getIconsData());
    }

    public function testGetImagesData(): void
    {
        $media1 = $this->prophesize(MediaInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getOrder()->shouldBeCalled()->willReturn(3);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());

        $media2 = $this->prophesize(MediaInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);
        $imageReference2 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference2->getOrder()->shouldBeCalled()->willReturn(1);
        $imageReference2->getMedia()->shouldBeCalled()->willReturn($media2->reveal());

        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [],
            [],
            [],
            [$imageReference1->reveal(), $imageReference2->reveal()]
        );

        $this->assertSame(['ids' => [2, 1]], $excerptView->getImagesData());
    }
}
