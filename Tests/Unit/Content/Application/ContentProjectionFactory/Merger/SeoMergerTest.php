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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentProjectionFactory\Merger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger\SeoMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
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

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(SeoInterface::class);
        $contentProjection->setSeoTitle(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementSeoInterface(): void
    {
        $merger = $this->getSeoMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);
        $dimensionContent->getSeoTitle(Argument::any())->shouldNotBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
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

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(SeoInterface::class);
        $contentProjection->setSeoTitle('Seo Title')->shouldBeCalled();
        $contentProjection->setSeoDescription('Seo Description')->shouldBeCalled();
        $contentProjection->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $contentProjection->setSeoCanonicalUrl('https://canonical.localhost/')->shouldBeCalled();
        $contentProjection->setSeoNoFollow(true)->shouldBeCalled();
        $contentProjection->setSeoNoIndex(true)->shouldBeCalled();
        $contentProjection->setSeoHideInSitemap(true)->shouldBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
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

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(SeoInterface::class);
        $contentProjection->setSeoTitle('Seo Title')->shouldNotBeCalled();
        $contentProjection->setSeoDescription('Seo Description')->shouldNotBeCalled();
        $contentProjection->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldNotBeCalled();
        $contentProjection->setSeoCanonicalUrl('https://canonical.localhost/')->shouldNotBeCalled();
        $contentProjection->setSeoNoFollow(false)->shouldNotBeCalled();
        $contentProjection->setSeoNoIndex(false)->shouldNotBeCalled();
        $contentProjection->setSeoHideInSitemap(false)->shouldNotBeCalled();

        $seoMerger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }
}
