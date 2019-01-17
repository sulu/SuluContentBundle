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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimension;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptDimensionTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testGetDimension(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals($dimensionIdentifier->reveal(), $excerptDimension->getDimensionIdentifier());
    }

    public function testGetResourceKey(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals(self::RESOURCE_KEY, $excerptDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals('resource-1', $excerptDimension->getResourceId());
    }

    public function testGetTitle(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($excerptDimension->getTitle());
    }

    public function testSetTitle(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals($excerptDimension, $excerptDimension->setTitle('title-1'));
        $this->assertEquals('title-1', $excerptDimension->getTitle());
    }

    public function testGetMore(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($excerptDimension->getMore());
    }

    public function testSetMore(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals($excerptDimension, $excerptDimension->setMore('more-1'));
        $this->assertEquals('more-1', $excerptDimension->getMore());
    }

    public function testGetDescription(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertNull($excerptDimension->getDescription());
    }

    public function testSetDescription(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals($excerptDimension, $excerptDimension->setDescription('description-1'));
        $this->assertEquals('description-1', $excerptDimension->getDescription());
    }

    public function testGetCategories(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals([], $excerptDimension->getCategories());
    }

    public function testAddCategory(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $category = $this->prophesize(CategoryInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addCategory($category->reveal()));
        $this->assertEquals([$category->reveal()], $excerptDimension->getCategories());
    }

    public function testClearCategories(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $category = $this->prophesize(CategoryInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addCategory($category->reveal()));
        $this->assertEquals($excerptDimension, $excerptDimension->clearCategories());
        $this->assertEquals([], $excerptDimension->getCategories());
    }

    public function testGetTags(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals([], $excerptDimension->getTags());
    }

    public function testAddTag(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $tag = $this->prophesize(TagInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addTag($tag->reveal()));
        $this->assertEquals([$tag->reveal()], $excerptDimension->getTags());
    }

    public function testClearTags(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $tag = $this->prophesize(TagInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addTag($tag->reveal()));
        $this->assertEquals($excerptDimension, $excerptDimension->clearTags());
        $this->assertEquals([], $excerptDimension->getTags());
    }

    public function testGetIcons(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals([], $excerptDimension->getIcons());
    }

    public function testAddIcon(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $icon = $this->prophesize(MediaInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addIcon($icon->reveal()));
        $this->assertEquals([$icon->reveal()], $excerptDimension->getIcons());
    }

    public function testClearIcons(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $icon = $this->prophesize(MediaInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addIcon($icon->reveal()));
        $this->assertEquals($excerptDimension, $excerptDimension->clearIcons());
        $this->assertEquals([], $excerptDimension->getIcons());
    }

    public function testGetImages(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertEquals([], $excerptDimension->getImages());
    }

    public function testAddImage(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $image = $this->prophesize(MediaInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addImage($image->reveal()));
        $this->assertEquals([$image->reveal()], $excerptDimension->getImages());
    }

    public function testClearImages(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $image = $this->prophesize(MediaInterface::class);

        $this->assertEquals($excerptDimension, $excerptDimension->addImage($image->reveal()));
        $this->assertEquals($excerptDimension, $excerptDimension->clearImages());
        $this->assertEquals([], $excerptDimension->getImages());
    }
}
