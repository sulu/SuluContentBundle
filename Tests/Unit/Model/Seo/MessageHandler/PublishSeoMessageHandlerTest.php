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
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
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
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(PublishSeoMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimension = $this->prophesize(DimensionInterface::class);
        $localizedLiveDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimension->reveal());

        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedLiveDimension->reveal());

        $localizedDraftSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedLiveSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedLiveSeo->copyAttributesFrom($localizedDraftSeo->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());

        $seoDimensionRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimension->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftSeo);

        $seoDimensionRepository->findOrCreate(self::RESOURCE_KEY, 'seo-1', $localizedLiveDimension->reveal())
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
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(PublishSeoMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('seo-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimension = $this->prophesize(DimensionInterface::class);
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimension->reveal());

        $seoDimensionRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimension->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
