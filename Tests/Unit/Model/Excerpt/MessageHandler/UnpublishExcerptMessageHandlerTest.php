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
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\UnpublishExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\UnpublishExcerptMessageHandler;

class UnpublishExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new UnpublishExcerptMessageHandler(
            $excerptDimensionRepository->reveal(), $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(UnpublishExcerptMessage::class);
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

        $localizedExcerpt = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findDimension(
            self::RESOURCE_KEY,
            'resource-1',
            $localizedDimensionIdentifier->reveal()
        )->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptDimensionRepository->removeDimension($localizedExcerpt->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
