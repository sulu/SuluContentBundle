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
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptDimensionTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testCreateClone(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $newExcerptDimension = $excerptDimension->createClone('new-resource-1');

        $this->assertSame('new-resource-1', $newExcerptDimension->getResourceId());
    }

    public function testGetDimension(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame($dimensionIdentifier->reveal(), $excerptDimension->getDimensionIdentifier());
    }

    public function testGetResourceKey(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame(self::RESOURCE_KEY, $excerptDimension->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame('resource-1', $excerptDimension->getResourceId());
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

        $this->assertSame($excerptDimension, $excerptDimension->setTitle('title-1'));
        $this->assertSame('title-1', $excerptDimension->getTitle());
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

        $this->assertSame($excerptDimension, $excerptDimension->setMore('more-1'));
        $this->assertSame('more-1', $excerptDimension->getMore());
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

        $this->assertSame($excerptDimension, $excerptDimension->setDescription('description-1'));
        $this->assertSame('description-1', $excerptDimension->getDescription());
    }

    public function testGetCategories(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame([], $excerptDimension->getCategories());
    }

    public function testGetCategory(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->shouldBeCalled()->willReturn(2);

        $this->assertSame($excerptDimension, $excerptDimension->addCategory($category1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->addCategory($category2->reveal()));
        $this->assertSame($category2->reveal(), $excerptDimension->getCategory(2));
        $this->assertNull($excerptDimension->getCategory(3));
    }

    public function testAddCategory(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $this->assertSame($excerptDimension, $excerptDimension->addCategory($category1->reveal()));
        $this->assertSame([$category1->reveal()], $excerptDimension->getCategories());
    }

    public function testRemoveCategory(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $this->assertSame($excerptDimension, $excerptDimension->addCategory($category1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->removeCategory($category1->reveal()));
        $this->assertSame([], $excerptDimension->getCategories());
    }

    public function testGetTags(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame([], $excerptDimension->getTags());
    }

    public function testGetTag(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->shouldBeCalled()->willReturn(1);
        $tag1->getName()->shouldBeCalled()->willReturn('tag-1');
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getId()->shouldBeCalled()->willReturn(2);
        $tag2->getName()->shouldBeCalled()->willReturn('tag-2');
        $tagReference2 = $this->prophesize(TagReferenceInterface::class);
        $tagReference2->getTag()->shouldBeCalled()->willReturn($tag2->reveal());

        $this->assertSame($excerptDimension, $excerptDimension->addTag($tagReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->addTag($tagReference2->reveal()));
        $this->assertSame($tagReference2->reveal(), $excerptDimension->getTag('tag-2'));
        $this->assertNull($excerptDimension->getTag('tag-3'));
    }

    public function testAddTag(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->shouldBeCalled()->willReturn(1);
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $this->assertSame($excerptDimension, $excerptDimension->addTag($tagReference1->reveal()));
        $this->assertSame([$tagReference1->reveal()], $excerptDimension->getTags());
    }

    public function testRemoveTag(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->shouldBeCalled()->willReturn(1);
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $this->assertSame($excerptDimension, $excerptDimension->addTag($tagReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->removeTag($tagReference1->reveal()));
        $this->assertSame([], $excerptDimension->getTags());
    }

    public function testGetIcons(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame([], $excerptDimension->getIcons());
    }

    public function testGetIcon(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $icon1 = $this->prophesize(MediaInterface::class);
        $icon1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($icon1);

        $icon2 = $this->prophesize(MediaInterface::class);
        $icon2->getId()->shouldBeCalled()->willReturn(2);
        $iconReference2 = $this->prophesize(IconReferenceInterface::class);
        $iconReference2->getMedia()->shouldBeCalled()->willReturn($icon2);

        $this->assertSame($excerptDimension, $excerptDimension->addIcon($iconReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->addIcon($iconReference2->reveal()));
        $this->assertSame($iconReference2->reveal(), $excerptDimension->getIcon(2));
        $this->assertNull($excerptDimension->getIcon(3));
    }

    public function testAddIcon(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $icon1 = $this->prophesize(MediaInterface::class);
        $icon1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($icon1);

        $this->assertSame($excerptDimension, $excerptDimension->addIcon($iconReference1->reveal()));
        $this->assertSame([$iconReference1->reveal()], $excerptDimension->getIcons());
    }

    public function testRemoveIcon(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $icon1 = $this->prophesize(MediaInterface::class);
        $icon1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($icon1);

        $this->assertSame($excerptDimension, $excerptDimension->addIcon($iconReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->removeIcon($iconReference1->reveal()));
        $this->assertSame([], $excerptDimension->getIcons());
    }

    public function testGetImages(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $this->assertSame([], $excerptDimension->getImages());
    }

    public function testGetImage(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $image1 = $this->prophesize(MediaInterface::class);
        $image1->getId()->shouldBeCalled()->willReturn(1);
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($image1);

        $image2 = $this->prophesize(MediaInterface::class);
        $image2->getId()->shouldBeCalled()->willReturn(2);
        $imageReference2 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference2->getMedia()->shouldBeCalled()->willReturn($image2);

        $this->assertSame($excerptDimension, $excerptDimension->addImage($imageReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->addImage($imageReference2->reveal()));
        $this->assertSame($imageReference2->reveal(), $excerptDimension->getImage(2));
        $this->assertNull($excerptDimension->getImage(3));
    }

    public function testAddImage(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $image1 = $this->prophesize(MediaInterface::class);
        $image1->getId()->shouldBeCalled()->willReturn(1);
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($image1);

        $this->assertSame($excerptDimension, $excerptDimension->addImage($imageReference1->reveal()));
        $this->assertSame([$imageReference1->reveal()], $excerptDimension->getImages());
    }

    public function testRemoveImage(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension($dimensionIdentifier->reveal(), self::RESOURCE_KEY, 'resource-1');

        $image1 = $this->prophesize(MediaInterface::class);
        $image1->getId()->shouldBeCalled()->willReturn(1);
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($image1);

        $this->assertSame($excerptDimension, $excerptDimension->addImage($imageReference1->reveal()));
        $this->assertSame($excerptDimension, $excerptDimension->removeImage($imageReference1->reveal()));
        $this->assertSame([], $excerptDimension->getImages());
    }

    public function testCopyAttributesFrom(): void
    {
        $dimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $excerptDimension = new ExcerptDimension(
            $dimensionIdentifier->reveal(),
            self::RESOURCE_KEY,
            'resource-1',
            'title-1',
            'more-1',
            'discription-1'
        );

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->shouldBeCalled()->willReturn(1);
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $icon1 = $this->prophesize(MediaInterface::class);
        $icon1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($icon1);

        $image1 = $this->prophesize(MediaInterface::class);
        $image1->getId()->shouldBeCalled()->willReturn(1);
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($image1);

        $otherDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $otherExcerptDimension = new ExcerptDimension(
            $otherDimensionIdentifier->reveal(),
            'other-resource-key',
            'other-resource-id',
            'other-title',
            'other-more',
            'other-description'
        );
        $otherExcerptDimension->addCategory($category1->reveal());
        $otherExcerptDimension->addTag($tagReference1->reveal());
        $otherExcerptDimension->addIcon($iconReference1->reveal());
        $otherExcerptDimension->addImage($imageReference1->reveal());

        $this->assertSame($excerptDimension, $excerptDimension->copyAttributesFrom($otherExcerptDimension));

        $this->assertSame($dimensionIdentifier->reveal(), $excerptDimension->getDimensionIdentifier());
        $this->assertSame(self::RESOURCE_KEY, $excerptDimension->getResourceKey());
        $this->assertSame('resource-1', $excerptDimension->getResourceId());
        $this->assertSame('other-title', $excerptDimension->getTitle());
        $this->assertSame('other-more', $excerptDimension->getMore());
        $this->assertSame('other-description', $excerptDimension->getDescription());
        $this->assertSame([], $excerptDimension->getCategories());
        $this->assertSame([], $excerptDimension->getTags());
        $this->assertSame([], $excerptDimension->getImages());
        $this->assertSame([], $excerptDimension->getIcons());
    }
}
