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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory\Merger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\ExcerptMerger;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ExcerptMergerTest extends TestCase
{
    protected function getExcerptMergerInstance(): MergerInterface
    {
        return new ExcerptMerger();
    }

    public function testMergeDimensionNotImplementExcerptInterface(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(ExcerptInterface::class);
        $contentView->setExcerptTitle(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeViewNotImplementExcerptInterface(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);
        $contentDimension->getExcerptTitle(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->willReturn(1);
        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getId()->willReturn(2);

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->willReturn(3);
        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->willReturn(4);

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);
        $contentDimension->getExcerptTitle()->willReturn('Excerpt Title')->shouldBeCalled();
        $contentDimension->getExcerptDescription()->willReturn('Excerpt Description')->shouldBeCalled();
        $contentDimension->getExcerptMore()->willReturn('Excerpt More')->shouldBeCalled();
        $contentDimension->getExcerptTags()->willReturn([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $contentDimension->getExcerptCategories()->willReturn([$category1->reveal(), $category2->reveal()])->shouldBeCalled();
        $contentDimension->getExcerptImage()->willReturn(['id' => 8])->shouldBeCalled();
        $contentDimension->getExcerptIcon()->willReturn(['id' => 9])->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(ExcerptInterface::class);
        $contentView->setExcerptTitle('Excerpt Title')->shouldBeCalled();
        $contentView->setExcerptDescription('Excerpt Description')->shouldBeCalled();
        $contentView->setExcerptMore('Excerpt More')->shouldBeCalled();
        $contentView->setExcerptTags(Argument::that(function ($tags) {
            return array_map(function (TagInterface $tag) {
                return $tag->getId();
            }, $tags) === [1, 2];
        }))->shouldBeCalled();
        $contentView->setExcerptCategories(Argument::that(function ($categories) {
            return array_map(function (CategoryInterface $category) {
                return $category->getId();
            }, $categories) === [3, 4];
        }))->shouldBeCalled();
        $contentView->setExcerptImage(['id' => 8])->shouldBeCalled();
        $contentView->setExcerptIcon(['id' => 9])->shouldBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeNotSet(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(ExcerptInterface::class);
        $contentDimension->getExcerptTitle()->willReturn(null)->shouldBeCalled();
        $contentDimension->getExcerptDescription()->willReturn(null)->shouldBeCalled();
        $contentDimension->getExcerptMore()->willReturn(null)->shouldBeCalled();
        $contentDimension->getExcerptTags()->willReturn([])->shouldBeCalled();
        $contentDimension->getExcerptCategories()->willReturn([])->shouldBeCalled();
        $contentDimension->getExcerptImage()->willReturn(null)->shouldBeCalled();
        $contentDimension->getExcerptIcon()->willReturn(null)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(ExcerptInterface::class);
        $contentView->setExcerptTitle(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptDescription(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptMore(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptTags(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptCategories(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptImage(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptIcon(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }
}
