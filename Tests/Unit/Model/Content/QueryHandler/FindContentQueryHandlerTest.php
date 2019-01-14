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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\QueryHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Query\FindContentQuery;
use Sulu\Bundle\ContentBundle\Model\Content\QueryHandler\FindContentQueryHandler;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;

class FindContentQueryHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testInvoke(): void
    {
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new FindContentQueryHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $query = $this->prophesize(FindContentQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);

        $contentRepository->findByDimensions(
            self::RESOURCE_KEY,
            'product-1',
            [$draftDimension->reveal(), $localizedDimension->reveal()]
        )->shouldBeCalled()->willReturn([$contentDimension1->reveal(), $contentDimension2->reveal()]);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentViewFactory->create(
            [$contentDimension1->reveal(), $contentDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn($contentView->reveal());

        $query->setContent($contentView->reveal())->shouldBeCalled();

        $handler->__invoke($query->reveal());
    }

    public function testInvokeContentNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new FindContentQueryHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $query = $this->prophesize(FindContentQuery::class);
        $query->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $query->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $query->getLocale()->shouldBeCalled()->willReturn('de');

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);

        $contentRepository->findByDimensions(
            self::RESOURCE_KEY,
            'product-1',
            [$draftDimension->reveal(), $localizedDimension->reveal()]
        )->shouldBeCalled()->willReturn([$contentDimension1->reveal(), $contentDimension2->reveal()]);

        $contentViewFactory->create(
            [$contentDimension1->reveal(), $contentDimension2->reveal()],
            'de'
        )->shouldBeCalled()->willReturn(null);

        $handler->__invoke($query->reveal());
    }
}
