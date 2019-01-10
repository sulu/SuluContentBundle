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
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\PublishContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\PublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\PublishSeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class PublishSeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testInvoke(): void
    {
        $seoRepository = $this->prophesize(SeoRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoRepository->reveal(),
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

        $localizedDraftSeo = $this->prophesize(SeoInterface::class);
        $localizedDraftSeo->getTitle()->shouldBeCalled()->willReturn('title-1');
        $localizedDraftSeo->getDescription()->shouldBeCalled()->willReturn('description-1');
        $localizedDraftSeo->getKeywords()->shouldBeCalled()->willReturn('keywords-1');
        $localizedDraftSeo->getCanonicalUrl()->shouldBeCalled()->willReturn(null);
        $localizedDraftSeo->getNoIndex()->shouldBeCalled()->willReturn(true);
        $localizedDraftSeo->getNoFollow()->shouldBeCalled()->willReturn(false);
        $localizedDraftSeo->getHideInSitemap()->shouldBeCalled()->willReturn(null);

        $localizedLiveSeo = $this->prophesize(SeoInterface::class);
        $localizedLiveSeo->setTitle('title-1')->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setDescription('description-1')->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setKeywords('keywords-1')->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setCanonicalUrl(null)->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setNoIndex(true)->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setNoFollow(false)->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());
        $localizedLiveSeo->setHideInSitemap(null)->shouldBeCalled()->willReturn($localizedLiveSeo->reveal());

        $seoRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimension->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftSeo);

        $seoRepository->findOrCreate(self::RESOURCE_KEY, 'seo-1', $localizedLiveDimension->reveal())
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

        $seoRepository = $this->prophesize(SeoRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new PublishSeoMessageHandler(
            $seoRepository->reveal(),
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

        $seoRepository->findByResource(self::RESOURCE_KEY, 'seo-1', $localizedDraftDimension->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
