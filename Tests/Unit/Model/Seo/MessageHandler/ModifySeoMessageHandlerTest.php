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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\ModifySeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\ModifySeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class ModifySeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testInvoke(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $seoViewFactory = $this->prophesize(SeoViewFactoryInterface::class);

        $handler = new ModifySeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(ModifySeoMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getTitle()->shouldBeCalled()->willReturn('title-1');
        $message->getDescription()->shouldBeCalled()->willReturn('description-1');
        $message->getKeywords()->shouldBeCalled()->willReturn('keywords-1');
        $message->getCanonicalUrl()->shouldBeCalled()->willReturn(null);
        $message->getNoIndex()->shouldBeCalled()->willReturn(true);
        $message->getNoFollow()->shouldBeCalled()->willReturn(false);
        $message->getHideInSitemap()->shouldBeCalled()->willReturn(null);

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $localizedSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedSeo->setTitle('title-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setDescription('description-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setKeywords('keywords-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setCanonicalUrl(null)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setNoIndex(true)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setNoFollow(false)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setHideInSitemap(null)->shouldBeCalled()->willReturn($localizedSeo->reveal());

        $seoDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedSeo->reveal());

        $seoView = $this->prophesize(SeoViewInterface::class);
        $seoViewFactory->create([$localizedSeo->reveal()], 'de')
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

        $handler = new ModifySeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $seoViewFactory->reveal()
        );

        $message = $this->prophesize(ModifySeoMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getTitle()->shouldBeCalled()->willReturn('title-1');
        $message->getDescription()->shouldBeCalled()->willReturn('description-1');
        $message->getKeywords()->shouldBeCalled()->willReturn('keywords-1');
        $message->getCanonicalUrl()->shouldBeCalled()->willReturn(null);
        $message->getNoIndex()->shouldBeCalled()->willReturn(true);
        $message->getNoFollow()->shouldBeCalled()->willReturn(false);
        $message->getHideInSitemap()->shouldBeCalled()->willReturn(null);

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $localizedSeo = $this->prophesize(SeoDimensionInterface::class);
        $localizedSeo->setTitle('title-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setDescription('description-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setKeywords('keywords-1')->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setCanonicalUrl(null)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setNoIndex(true)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setNoFollow(false)->shouldBeCalled()->willReturn($localizedSeo->reveal());
        $localizedSeo->setHideInSitemap(null)->shouldBeCalled()->willReturn($localizedSeo->reveal());

        $seoDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedSeo->reveal());

        $seoViewFactory->create([$localizedSeo->reveal()], 'de')
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
