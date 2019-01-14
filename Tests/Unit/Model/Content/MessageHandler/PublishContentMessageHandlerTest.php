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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class PublishContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testInvoke(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new PublishContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(PublishContentMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $draftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $liveDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $localizedLiveDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimensionIdentifier->reveal());
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE]
        )->shouldBeCalled()->willReturn($liveDimensionIdentifier->reveal());
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedLiveDimensionIdentifier->reveal());

        $draftContent = $this->prophesize(ContentDimensionInterface::class);
        $draftContent->getType()->shouldBeCalled()->willReturn('default');
        $liveContent = $this->prophesize(ContentDimensionInterface::class);
        $liveContent->copyAttributesFrom($draftContent->reveal())->shouldBeCalled();

        $localizedDraftContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedDraftContent->getType()->shouldBeCalled()->willReturn('default');
        $localizedLiveContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedLiveContent->copyAttributesFrom($localizedDraftContent->reveal())->shouldBeCalled();

        $contentDimensionRepository->findDimension(self::RESOURCE_KEY, 'product-1', $draftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($draftContent);

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'product-1', $liveDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($liveContent);

        $contentDimensionRepository->findDimension(self::RESOURCE_KEY, 'product-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftContent);

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'product-1', $localizedLiveDimensionIdentifier->reveal())
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

        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new PublishContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(PublishContentMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $draftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimensionIdentifier->reveal());

        $contentDimensionRepository->findDimension(self::RESOURCE_KEY, 'product-1', $draftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
