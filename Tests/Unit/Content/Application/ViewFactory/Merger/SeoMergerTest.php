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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementSeoInterface(): void
    {
        $merger = $this->getSeoMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);
        $dimensionContent->getSeoTitle(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getSeoMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);
        $dimensionContent->getSeoTitle()->willReturn('Seo Title')->shouldBeCalled();
        $dimensionContent->getSeoDescription()->willReturn('Seo Description')->shouldBeCalled();
        $dimensionContent->getSeoKeywords()->willReturn('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $dimensionContent->getSeoCanonicalUrl()->willReturn('https://canonical.localhost/')->shouldBeCalled();
        $dimensionContent->getSeoNoFollow()->willReturn(true)->shouldBeCalled();
        $dimensionContent->getSeoNoIndex()->willReturn(true)->shouldBeCalled();
        $dimensionContent->getSeoHideInSitemap()->willReturn(true)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle('Seo Title')->shouldBeCalled();
        $contentView->setSeoDescription('Seo Description')->shouldBeCalled();
        $contentView->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $contentView->setSeoCanonicalUrl('https://canonical.localhost/')->shouldBeCalled();
        $contentView->setSeoNoFollow(true)->shouldBeCalled();
        $contentView->setSeoNoIndex(true)->shouldBeCalled();
        $contentView->setSeoHideInSitemap(true)->shouldBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeNotSet(): void
    {
        $seoMerger = $this->getSeoMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);
        $dimensionContent->getSeoTitle()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getSeoDescription()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getSeoKeywords()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getSeoCanonicalUrl()->willReturn(null)->shouldBeCalled();
        $dimensionContent->getSeoNoFollow()->willReturn(false)->shouldBeCalled();
        $dimensionContent->getSeoNoIndex()->willReturn(false)->shouldBeCalled();
        $dimensionContent->getSeoHideInSitemap()->willReturn(false)->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(SeoInterface::class);
        $contentView->setSeoTitle('Seo Title')->shouldNotBeCalled();
        $contentView->setSeoDescription('Seo Description')->shouldNotBeCalled();
        $contentView->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldNotBeCalled();
        $contentView->setSeoCanonicalUrl('https://canonical.localhost/')->shouldNotBeCalled();
        $contentView->setSeoNoFollow(false)->shouldNotBeCalled();
        $contentView->setSeoNoIndex(false)->shouldNotBeCalled();
        $contentView->setSeoHideInSitemap(false)->shouldNotBeCalled();

        $seoMerger->merge($contentView->reveal(), $dimensionContent->reveal());
    }
}
