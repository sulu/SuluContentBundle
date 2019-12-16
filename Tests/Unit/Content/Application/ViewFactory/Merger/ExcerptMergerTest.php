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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(ExcerptInterface::class);
        $contentView->setExcerptTitle(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementExcerptInterface(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);
        $dimensionContent->getExcerptTitle(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);
        $dimensionContent->getExcerptTitle()->willReturn('Excerpt Title')->shouldBeCalled();
        $dimensionContent->getExcerptDescription()->willReturn('Excerpt Description')->shouldBeCalled();
        $dimensionContent->getExcerptMore()->willReturn('Excerpt More')->shouldBeCalled();
        $dimensionContent->getExcerptTags()->willReturn([$tag1->reveal(), $tag2->reveal()])->shouldBeCalled();
        $dimensionContent->getExcerptCategories()->willReturn([$category1->reveal(), $category2->reveal()])->shouldBeCalled();
        $dimensionContent->getExcerptImage()->willReturn(['id' => 8])->shouldBeCalled();
        $dimensionContent->getExcerptIcon()->willReturn(['id' => 9])->shouldBeCalled();

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

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeNotSet(): void
    {
        $merger = $this->getExcerptMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(ExcerptInterface::class);
        $dimensionContent->getExcerptTitle()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getExcerptDescription()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getExcerptMore()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getExcerptTags()->willReturn([])->shouldBeCalled();
        $dimensionContent->getExcerptCategories()->willReturn([])->shouldBeCalled();
        $dimensionContent->getExcerptImage()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getExcerptIcon()->willReturn(null)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(ExcerptInterface::class);
        $contentView->setExcerptTitle(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptDescription(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptMore(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptTags(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptCategories(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptImage(Argument::any())->shouldNotBeCalled();
        $contentView->setExcerptIcon(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }
}
