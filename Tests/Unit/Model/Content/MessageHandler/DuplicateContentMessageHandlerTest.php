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
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Message\DuplicateContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\DuplicateContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class DuplicateContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getNewResourceId()->shouldBeCalled()->willReturn('new-resource-1');

        $draftDimensionIdentifierEN = $this->prophesize(DimensionIdentifierInterface::class);
        $draftDimensionIdentifierDE = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findByPartialAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
            ]
        )->shouldBeCalled()->willReturn(
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        );

        $draftContentEN = $this->prophesize(ContentDimensionInterface::class);
        $draftContentDE = $this->prophesize(ContentDimensionInterface::class);

        $contentDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([$draftContentEN->reveal(), $draftContentDE->reveal()]);

        $contentDimensionRepository->createClone($draftContentEN->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $contentDimensionRepository->createClone($draftContentDE->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeContentNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $draftDimensionIdentifierEN = $this->prophesize(DimensionIdentifierInterface::class);
        $draftDimensionIdentifierDE = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findByPartialAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
            ]
        )->shouldBeCalled()->willReturn(
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        );

        $contentDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }

    public function testInvokeContentNotFoundNotMandatory(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->isMandatory()->shouldBeCalled()->willReturn(false);

        $draftDimensionIdentifierEN = $this->prophesize(DimensionIdentifierInterface::class);
        $draftDimensionIdentifierDE = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findByPartialAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
            ]
        )->shouldBeCalled()->willReturn(
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        );

        $contentDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }
}
