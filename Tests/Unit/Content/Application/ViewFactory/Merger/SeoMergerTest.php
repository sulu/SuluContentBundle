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
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\SeoMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMergerTest extends TestCase
{
    protected function getSeoMergerInstance(): MergerInterface
    {
        return new SeoMerger();
    }

    public function testMergeDimensionNotImplementSeoInterface(): void
    {
        $merger = $this->getSeoMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeViewNotImplementSeoInterface(): void
    {
        $merger = $this->getSeoMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);
        $contentDimension->getSeoTitle(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getSeoMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);
        $contentDimension->getSeoTitle()->willReturn('Seo Title')->shouldBeCalled();
        $contentDimension->getSeoDescription()->willReturn('Seo Description')->shouldBeCalled();
        $contentDimension->getSeoKeywords()->willReturn('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $contentDimension->getSeoCanonicalUrl()->willReturn('https://canonical.localhost/')->shouldBeCalled();
        $contentDimension->getSeoNoFollow()->willReturn(true)->shouldBeCalled();
        $contentDimension->getSeoNoIndex()->willReturn(true)->shouldBeCalled();
        $contentDimension->getSeoHideInSitemap()->willReturn(true)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle('Seo Title')->shouldBeCalled();
        $contentView->setSeoDescription('Seo Description')->shouldBeCalled();
        $contentView->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $contentView->setSeoCanonicalUrl('https://canonical.localhost/')->shouldBeCalled();
        $contentView->setSeoNoFollow(true)->shouldBeCalled();
        $contentView->setSeoNoIndex(true)->shouldBeCalled();
        $contentView->setSeoHideInSitemap(true)->shouldBeCalled();

        $merger->merge($contentView->reveal(), $contentDimension->reveal());
    }

    public function testMergeNotSet(): void
    {
        $seoMerger = $this->getSeoMergerInstance();

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);
        $contentDimension->getSeoTitle()->willReturn(null)->shouldBeCalled();
        $contentDimension->getSeoDescription()->willReturn(null)->shouldBeCalled();
        $contentDimension->getSeoKeywords()->willReturn(null)->shouldBeCalled();
        $contentDimension->getSeoCanonicalUrl()->willReturn(null)->shouldBeCalled();
        $contentDimension->getSeoNoFollow()->willReturn(false)->shouldBeCalled();
        $contentDimension->getSeoNoIndex()->willReturn(false)->shouldBeCalled();
        $contentDimension->getSeoHideInSitemap()->willReturn(false)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle('Seo Title')->shouldNotBeCalled();
        $contentView->setSeoDescription('Seo Description')->shouldNotBeCalled();
        $contentView->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldNotBeCalled();
        $contentView->setSeoCanonicalUrl('https://canonical.localhost/')->shouldNotBeCalled();
        $contentView->setSeoNoFollow(false)->shouldNotBeCalled();
        $contentView->setSeoNoIndex(false)->shouldNotBeCalled();
        $contentView->setSeoHideInSitemap(false)->shouldNotBeCalled();

        $seoMerger->merge($contentView->reveal(), $contentDimension->reveal());
    }
}
