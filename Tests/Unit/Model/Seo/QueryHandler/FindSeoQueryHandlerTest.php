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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\QueryHandler\FindSeoQueryHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class FindSeoQueryHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testInvoke(): void
    {
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new FindSeoQueryHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $query = $this->prophesize(FindSeoQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $seoDimension1 = $this->prophesize(SeoDimensionInterface::class);
        $seoDimension2 = $this->prophesize(SeoDimensionInterface::class);

        $seoDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'seo-1',
            [$localizedDimensionIdentifier->reveal()]
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

        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new FindSeoQueryHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $query = $this->prophesize(FindSeoQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $seoDimension1 = $this->prophesize(SeoDimensionInterface::class);
        $seoDimension2 = $this->prophesize(SeoDimensionInterface::class);

        $seoDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'seo-1',
            [$localizedDimensionIdentifier->reveal()]
        )->shouldBeCalled()->willReturn([$seoDimension1->reveal(), $seoDimension2->reveal()]);

        $seoViewFactory->create(
            [$seoDimension1->reveal(), $seoDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn(null);

        $handler->__invoke($query->reveal());
    }
}
