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
use Sulu\Bundle\ContentBundle\Model\Content\Message\UnpublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\UnpublishContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class UnpublishContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new UnpublishContentMessageHandler(
            $contentDimensionRepository->reveal(), $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(UnpublishContentMessage::class);
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

        $localizedContent = $this->prophesize(ContentDimensionInterface::class);

        $contentDimensionRepository->findDimension(
            self::RESOURCE_KEY,
            'resource-1',
            $localizedDimensionIdentifier->reveal()
        )->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentDimensionRepository->removeDimension($localizedContent->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
