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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDimensionCollection\Mapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\SeoMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMapperTest extends TestCase
{
    protected function createSeoMapperInstance()
    {
        return new SeoMapper();
    }

    public function testMapNoSeo(): void
    {
        $data = [
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoCanonicalUrl' => 'http://example.localhost',
            'seoHideInSitemap' => true,
            'seoNoIndex' => true,
            'seoNoFollow' => true,
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $seoMapper = $this->createSeoMapperInstance();
        $this->assertNull(
            $seoMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal())
        );
    }

    public function testMapLocalizedNoSeo(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoCanonicalUrl' => 'http://example.localhost',
            'seoHideInSitemap' => true,
            'seoNoIndex' => true,
            'seoNoFollow' => true,
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $seoMapper = $this->createSeoMapperInstance();

        $this->assertNull(
            $seoMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal())
        );
    }

    public function testMapUnlocalizedSeo(): void
    {
        $data = [
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoCanonicalUrl' => 'http://example.localhost',
            'seoHideInSitemap' => true,
            'seoNoIndex' => true,
            'seoNoFollow' => true,
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);
        $contentDimension->setSeoTitle('Seo Title')->shouldBeCalled();
        $contentDimension->setSeoDescription('Seo Description')->shouldBeCalled();
        $contentDimension->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $contentDimension->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $contentDimension->setSeoHideInSitemap(true)->shouldBeCalled();
        $contentDimension->setSeoNoIndex(true)->shouldBeCalled();
        $contentDimension->setSeoNoFollow(true)->shouldBeCalled();

        $seoMapper = $this->createSeoMapperInstance();

        $seoMapper->map($data, $contentDimension->reveal());
    }

    public function testMapLocalizedSeo(): void
    {
        $data = [
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoCanonicalUrl' => 'http://example.localhost',
            'seoHideInSitemap' => true,
            'seoNoIndex' => true,
            'seoNoFollow' => true,
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(SeoInterface::class);

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(SeoInterface::class);
        $localizedContentDimension->setSeoTitle('Seo Title')->shouldBeCalled();
        $localizedContentDimension->setSeoDescription('Seo Description')->shouldBeCalled();
        $localizedContentDimension->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $localizedContentDimension->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $localizedContentDimension->setSeoHideInSitemap(true)->shouldBeCalled();
        $localizedContentDimension->setSeoNoIndex(true)->shouldBeCalled();
        $localizedContentDimension->setSeoNoFollow(true)->shouldBeCalled();

        $seoMapper = $this->createSeoMapperInstance();

        $seoMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }
}
