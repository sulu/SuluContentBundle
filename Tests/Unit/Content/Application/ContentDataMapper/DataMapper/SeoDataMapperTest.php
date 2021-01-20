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
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\SeoDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoDataMapperTest extends TestCase
{
    protected function createSeoDataMapperInstance(): SeoDataMapper
    {
        return new SeoDataMapper();
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $seoMapper = $this->createSeoDataMapperInstance();
        $seoMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(SeoInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $seoMapper = $this->createSeoDataMapperInstance();

        $seoMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(SeoInterface::class);
        $unlocalizedDimensionContent->setSeoTitle('Seo Title')->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoDescription('Seo Description')->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoHideInSitemap(true)->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoNoIndex(true)->shouldBeCalled();
        $unlocalizedDimensionContent->setSeoNoFollow(true)->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $seoMapper = $this->createSeoDataMapperInstance();

        $seoMapper->map($data, $dimensionContentCollection->reveal());
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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(SeoInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(SeoInterface::class);
        $localizedDimensionContent->setSeoTitle('Seo Title')->shouldBeCalled();
        $localizedDimensionContent->setSeoDescription('Seo Description')->shouldBeCalled();
        $localizedDimensionContent->setSeoKeywords('Seo Keyword 1, Seo Keyword 2')->shouldBeCalled();
        $localizedDimensionContent->setSeoCanonicalUrl('http://example.localhost')->shouldBeCalled();
        $localizedDimensionContent->setSeoHideInSitemap(true)->shouldBeCalled();
        $localizedDimensionContent->setSeoNoIndex(true)->shouldBeCalled();
        $localizedDimensionContent->setSeoNoFollow(true)->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $seoMapper = $this->createSeoDataMapperInstance();

        $seoMapper->map($data, $dimensionContentCollection->reveal());
    }
}
