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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\DimensionContentCollectionFactory\DataMapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentCollectionFactory\DataMapper\ExcerptDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptDataMapperTest extends TestCase
{
    protected function createExcerptDataMapperInstance(
        TagFactoryInterface $tagFactory,
        CategoryFactoryInterface $categoryFactory
    ): ExcerptDataMapper {
        return new ExcerptDataMapper($tagFactory, $categoryFactory);
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());
        $excerptMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);
        $dimensionContent->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $dimensionContent->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $dimensionContent->setExcerptMore('Excerpt More')->shouldBeCalled();
        $dimensionContent->setExcerptImage(['id' => 1])->shouldBeCalled();
        $dimensionContent->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $dimensionContent->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $dimensionContent->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(ExcerptInterface::class);
        $localizedDimensionContent->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $localizedDimensionContent->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $localizedDimensionContent->setExcerptMore('Excerpt More')->shouldBeCalled();
        $localizedDimensionContent->setExcerptImage(['id' => 1])->shouldBeCalled();
        $localizedDimensionContent->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $localizedDimensionContent->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $localizedDimensionContent->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }
}
