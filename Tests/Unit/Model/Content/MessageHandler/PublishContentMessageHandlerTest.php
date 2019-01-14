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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\PublishContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;

class PublishContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testInvoke(): void
    {
        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new PublishContentMessageHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(PublishContentMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $liveDimension = $this->prophesize(DimensionInterface::class);

        $localizedDraftDimension = $this->prophesize(DimensionInterface::class);
        $localizedLiveDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimension->reveal());

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_LIVE]
        )->shouldBeCalled()->willReturn($liveDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedLiveDimension->reveal());

        $draftContent = $this->prophesize(ContentDimensionInterface::class);
        $draftContent->getType()->shouldBeCalled()->willReturn('default');
        $liveContent = $this->prophesize(ContentDimensionInterface::class);
        $liveContent->copyAttributesFrom($draftContent->reveal())->shouldBeCalled();

        $localizedDraftContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedDraftContent->getType()->shouldBeCalled()->willReturn('default');
        $localizedLiveContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedLiveContent->copyAttributesFrom($localizedDraftContent->reveal())->shouldBeCalled();

        $contentRepository->findByResource(self::RESOURCE_KEY, 'product-1', $draftDimension->reveal())
            ->shouldBeCalled()->willReturn($draftContent);

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $liveDimension->reveal())
            ->shouldBeCalled()->willReturn($liveContent);

        $contentRepository->findByResource(self::RESOURCE_KEY, 'product-1', $localizedDraftDimension->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftContent);

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $localizedLiveDimension->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveContent);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentViewFactory->create([$liveContent->reveal(), $localizedLiveContent->reveal()], 'en')
            ->shouldBeCalled()->willReturn($contentView->reveal());

        $message->setContent($contentView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeContentNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new PublishContentMessageHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(PublishContentMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());

        $contentRepository->findByResource(self::RESOURCE_KEY, 'product-1', $draftDimension->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
