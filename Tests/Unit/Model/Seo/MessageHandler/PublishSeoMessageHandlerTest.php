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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\PublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\PublishSeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class PublishSeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testInvoke(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(PublishSeoMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('seo-1');
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

        $localizedDraftSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedLiveSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedLiveSeo->copyAttributesFrom($localizedDraftSeo->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());

        $seoDimensionRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftSeo);

        $seoDimensionRepository->findOrCreate(self::RESOURCE_KEY, 'seo-1', $localizedLiveDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveSeo);

        $seoView = $this->prophesize(SeoViewInterface::class);
        $seoViewFactory->create([$localizedLiveSeo->reveal()], 'en')
            ->shouldBeCalled()->willReturn($seoView->reveal());

        $message->setSeo($seoView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeSeoNotFound(): void
    {
        $this->expectException(SeoNotFoundException::class);

        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(PublishSeoMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $seoDimensionRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
