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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\UnpublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\UnpublishSeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class UnpublishSeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new UnpublishSeoMessageHandler(
            $seoDimensionRepository->reveal(), $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(UnpublishSeoMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $seo = $this->prophesize(SeoDimensionInterface::class);
        $localizedSeo = $this->prophesize(SeoDimensionInterface::class);

        $seoDimensionRepository->findDimension(
            self::RESOURCE_KEY,
            'resource-1',
            $localizedDimensionIdentifier->reveal()
        )->shouldBeCalled()->willReturn($localizedSeo->reveal());

        $seoDimensionRepository->removeDimension($localizedSeo->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
