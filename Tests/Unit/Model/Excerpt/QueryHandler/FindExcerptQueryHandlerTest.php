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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\QueryHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Query\FindExcerptQuery;
use Sulu\Bundle\ContentBundle\Model\Excerpt\QueryHandler\FindExcerptQueryHandler;

class FindExcerptQueryHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new FindExcerptQueryHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $query = $this->prophesize(FindExcerptQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $excerptDimension1 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension2 = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$localizedDimensionIdentifier->reveal()]
        )->shouldBeCalled()->willReturn([$excerptDimension1->reveal(), $excerptDimension2->reveal()]);

        $excerptView = $this->prophesize(ExcerptViewInterface::class);
        $excerptViewFactory->create(
            [$excerptDimension1->reveal(), $excerptDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn($excerptView->reveal());

        $query->setExcerpt($excerptView->reveal())->shouldBeCalled();

        $handler->__invoke($query->reveal());
    }

    public function testInvokeExcerptNotFound(): void
    {
        $this->expectException(ExcerptNotFoundException::class);

        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new FindExcerptQueryHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $query = $this->prophesize(FindExcerptQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $excerptDimension1 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension2 = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$localizedDimensionIdentifier->reveal()]
        )->shouldBeCalled()->willReturn([$excerptDimension1->reveal(), $excerptDimension2->reveal()]);

        $excerptViewFactory->create(
            [$excerptDimension1->reveal(), $excerptDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn(null);

        $handler->__invoke($query->reveal());
    }
}
