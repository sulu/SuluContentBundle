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
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\DuplicateExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\DuplicateExcerptMessageHandler;

class DuplicateExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateExcerptMessage::class);
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

        $draftExcerptEN = $this->prophesize(ExcerptDimensionInterface::class);
        $draftExcerptDE = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([$draftExcerptEN->reveal(), $draftExcerptDE->reveal()]);

        $excerptDimensionRepository->createClone($draftExcerptEN->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $excerptDimensionRepository->createClone($draftExcerptDE->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeExcerptNotFound(): void
    {
        $this->expectException(ExcerptNotFoundException::class);

        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateExcerptMessage::class);
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

        $excerptDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }

    public function testInvokeExcerptNotFoundNotMandatory(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateExcerptMessage::class);
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

        $excerptDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }
}
