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
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptViewTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    private $category1;
    private $category2;
    private $tag1;
    private $tag2;
    private $media1;
    private $media2;
    private $media3;

    public function setUp()
    {
        parent::setUp();

        $this->category1 = $this->prophesize(CategoryInterface::class);
        $this->category1->getId()->willReturn(1);

        $this->category2 = $this->prophesize(CategoryInterface::class);
        $this->category2->getId()->willReturn(2);

        $this->tag1 = $this->prophesize(TagInterface::class);
        $this->tag1->getName()->willReturn('tag-1');

        $this->tag2 = $this->prophesize(TagInterface::class);
        $this->tag2->getName()->willReturn('tag-2');

        $this->media1 = $this->prophesize(MediaInterface::class);
        $this->media1->getId()->willReturn(1);

        $this->media2 = $this->prophesize(MediaInterface::class);
        $this->media2->getId()->willReturn(2);

        $this->media3 = $this->prophesize(MediaInterface::class);
        $this->media3->getId()->willReturn(3);
    }

    public function testGetResourceKey(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals(self::RESOURCE_KEY, $excerptView->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals('resource-1', $excerptView->getResourceId());
    }

    public function testGetLocale(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals('en', $excerptView->getLocale());
    }

    public function testGetTitle(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals('title-1', $excerptView->getTitle());
    }

    public function testGetMore(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals('more-1', $excerptView->getMore());
    }

    public function testGetDescription(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertNull($excerptView->getDescription());
    }

    public function testGetCategoryIds(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals([1, 2], $excerptView->getCategoryIds());
    }

    public function testGetTagNames(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals(['tag-1', 'tag-2'], $excerptView->getTagNames());
    }

    public function testGetIconsData(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals(['ids' => [1, 2]], $excerptView->getIconsData());
    }

    public function testGetImagesData(): void
    {
        $excerptView = new ExcerptView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'more-1',
            null,
            [$this->category1->reveal(), $this->category2->reveal()],
            [$this->tag1->reveal(), $this->tag2->reveal()],
            [$this->media1->reveal(), $this->media2->reveal()],
            [$this->media2->reveal(), $this->media3->reveal()]
        );

        $this->assertEquals(['ids' => [2, 3]], $excerptView->getImagesData());
    }
}
