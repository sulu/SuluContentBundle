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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\QueryHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\QueryHandler\FindSeoQueryHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class FindSeoQueryHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testInvoke(): void
    {
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoRepository = $this->prophesize(SeoRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new FindSeoQueryHandler(
            $seoRepository->reveal(),
            $dimensionRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $query = $this->prophesize(FindSeoQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

        $seoDimension1 = $this->prophesize(SeoInterface::class);
        $seoDimension2 = $this->prophesize(SeoInterface::class);

        $seoRepository->findByDimensions(
            self::RESOURCE_KEY,
            'seo-1',
            [$localizedDimension->reveal()]
        )->shouldBeCalled()->willReturn([$seoDimension1->reveal(), $seoDimension2->reveal()]);

        $seoView = $this->prophesize(SeoViewInterface::class);
        $seoViewFactory->create(
            [$seoDimension1->reveal(), $seoDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn($seoView->reveal());

        $query->setSeo($seoView->reveal())->shouldBeCalled();

        $handler->__invoke($query->reveal());
    }

    public function testInvokeSeoNotFound(): void
    {
        $this->expectException(SeoNotFoundException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoRepository = $this->prophesize(SeoRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new FindSeoQueryHandler(
            $seoRepository->reveal(),
            $dimensionRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $query = $this->prophesize(FindSeoQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

        $seoDimension1 = $this->prophesize(SeoInterface::class);
        $seoDimension2 = $this->prophesize(SeoInterface::class);

        $seoRepository->findByDimensions(
            self::RESOURCE_KEY,
            'seo-1',
            [$localizedDimension->reveal()]
        )->shouldBeCalled()->willReturn([$seoDimension1->reveal(), $seoDimension2->reveal()]);

        $seoViewFactory->create(
            [$seoDimension1->reveal(), $seoDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn(null);

        $handler->__invoke($query->reveal());
    }
}
