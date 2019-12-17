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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\DimensionContentFactory\Mapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper\SeoMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMapperTest extends TestCase
{
    protected function createSeoMapperInstance(): SeoMapper
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $seoMapper = $this->createSeoMapperInstance();
        $seoMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
        $this->assertTrue(true); // Avoid risky test as this is an early return test
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $seoMapper = $this->createSeoMapperInstance();

        $seoMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);
        $dimensionContent->setSeoTitle('Seo Title')->shouldBeCalled();
        $dimensionContent->setSeoDescription('Seo Description')->shouldBeCalled();
        $dimensionContent->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $dimensionContent->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $dimensionContent->setSeoHideInSitemap(true)->shouldBeCalled();
        $dimensionContent->setSeoNoIndex(true)->shouldBeCalled();
        $dimensionContent->setSeoNoFollow(true)->shouldBeCalled();

        $seoMapper = $this->createSeoMapperInstance();

        $seoMapper->map($data, $dimensionContent->reveal());
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(SeoInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(SeoInterface::class);
        $localizedDimensionContent->setSeoTitle('Seo Title')->shouldBeCalled();
        $localizedDimensionContent->setSeoDescription('Seo Description')->shouldBeCalled();
        $localizedDimensionContent->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $localizedDimensionContent->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $localizedDimensionContent->setSeoHideInSitemap(true)->shouldBeCalled();
        $localizedDimensionContent->setSeoNoIndex(true)->shouldBeCalled();
        $localizedDimensionContent->setSeoNoFollow(true)->shouldBeCalled();

        $seoMapper = $this->createSeoMapperInstance();

        $seoMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }
}
