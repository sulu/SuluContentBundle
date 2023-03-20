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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDataMapper\DataMapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\ExcerptDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptDataMapperTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    protected function createExcerptDataMapperInstance(
        TagFactoryInterface $tagFactory,
        CategoryFactoryInterface $categoryFactory
    ): ExcerptDataMapper {
        return new ExcerptDataMapper($tagFactory, $categoryFactory);
    }

    public function testMapUnlocalizedNoExcerpt(): void
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());
        $excerptMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(ExcerptInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $tagFactory = $this->prophesize(TagFactoryInterface::class);
        $categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(ExcerptInterface::class);
        $unlocalizedDimensionContent->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptMore('Excerpt More')->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptImage(['id' => 1])->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $unlocalizedDimensionContent->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(ExcerptInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(ExcerptInterface::class);
        $localizedDimensionContent->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $localizedDimensionContent->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $localizedDimensionContent->setExcerptMore('Excerpt More')->shouldBeCalled();
        $localizedDimensionContent->setExcerptImage(['id' => 1])->shouldBeCalled();
        $localizedDimensionContent->setExcerptIcon(['id' => 2])->shouldBeCalled();
        $localizedDimensionContent->setExcerptTags([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $localizedDimensionContent->setExcerptCategories([$category1->reveal(), $category2->reveal()])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $excerptMapper = $this->createExcerptDataMapperInstance($tagFactory->reveal(), $categoryFactory->reveal());

        $excerptMapper->map($data, $dimensionContentCollection->reveal());
    }
}
