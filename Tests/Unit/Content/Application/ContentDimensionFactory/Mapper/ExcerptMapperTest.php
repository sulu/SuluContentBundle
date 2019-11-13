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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDimensionFactory\Mapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\ExcerptMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptMapperTest extends TestCase
{
    protected function createExcerptMapperInstance(
        TagFactoryInterface $tagFactory,
        CategoryFactoryInterface $categoryFactory
    ): ExcerptMapper {
        return new ExcerptMapper($tagFactory, $categoryFactory);
    }

    public function testMapNoExcerpt(): void
    {
        $data = [
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptImage' => ['id' => 1],
            'excerptIcon' => ['id' => 2],
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [3, 4],
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());
        $excerptMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoExcerpt(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptImage' => ['id' => 1],
            'excerptIcon' => ['id' => 2],
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [3, 4],
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapUnlocalizedExcerpt(): void
    {
        $data = [
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptImage' => ['id' => 1],
            'excerptIcon' => ['id' => 2],
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [3, 4],
        ];

        $tag1 = $this->prophesize(TagInterface::class);
        $tag2 = $this->prophesize(TagInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $tagFactory->create(['Tag 1', 'Tag 2'])->willReturn([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();

        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);
        $category1 = $this->prophesize(TagInterface::class);
        $category2 = $this->prophesize(TagInterface::class);
        $categoryFactory->create([3, 4])->willReturn([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);
        $contentDimension->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $contentDimension->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $contentDimension->setExcerptMore('Excerpt More')->shouldBeCalled();
        $contentDimension->setExcerptImage(['id' => 1])->shouldBeCalled();
        $contentDimension->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $contentDimension->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $contentDimension->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $excerptMapper = $this->createExcerptMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $contentDimension->reveal());
    }

    public function testMapLocalizedExcerpt(): void
    {
        $data = [
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptImage' => ['id' => 1],
            'excerptIcon' => ['id' => 2],
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [3, 4],
        ];

        $tag1 = $this->prophesize(TagInterface::class);
        $tag2 = $this->prophesize(TagInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $tagFactory->create(['Tag 1', 'Tag 2'])->willReturn([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();

        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);
        $category1 = $this->prophesize(TagInterface::class);
        $category2 = $this->prophesize(TagInterface::class);
        $categoryFactory->create([3, 4])->willReturn([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(ExcerptInterface::class);
        $localizedContentDimension->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $localizedContentDimension->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $localizedContentDimension->setExcerptMore('Excerpt More')->shouldBeCalled();
        $localizedContentDimension->setExcerptImage(['id' => 1])->shouldBeCalled();
        $localizedContentDimension->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $localizedContentDimension->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $localizedContentDimension->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $excerptMapper = $this->createExcerptMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }
}
