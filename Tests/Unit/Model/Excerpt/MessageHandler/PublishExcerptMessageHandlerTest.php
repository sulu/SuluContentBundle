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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\PublishExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\PublishExcerptMessageHandler;

class PublishExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new PublishExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(PublishExcerptMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $localizedLiveDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedLiveDimensionIdentifier->reveal());

        $localizedDraftExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedLiveExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedLiveExcerpt->copyAttributesFrom($localizedDraftExcerpt->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        $excerptDimensionRepository->findDimension(self::RESOURCE_KEY, 'resource-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftExcerpt);

        $excerptDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedLiveDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveExcerpt);

        $excerptView = $this->prophesize(ExcerptViewInterface::class);
        $excerptViewFactory->create([$localizedLiveExcerpt->reveal()], 'en')
            ->shouldBeCalled()->willReturn($excerptView->reveal());

        $message->setExcerpt($excerptView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeExcerptNotFound(): void
    {
        $this->expectException(ExcerptNotFoundException::class);

        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new PublishExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(PublishExcerptMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $excerptDimensionRepository->findDimension(self::RESOURCE_KEY, 'resource-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
