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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\DuplicateSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\DuplicateSeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class DuplicateSeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testInvoke(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateSeoMessage::class);
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

        $draftSeoEN = $this->prophesize(SeoDimensionInterface::class);
        $draftSeoDE = $this->prophesize(SeoDimensionInterface::class);

        $seoDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([$draftSeoEN->reveal(), $draftSeoDE->reveal()]);

        $seoDimensionRepository->createClone($draftSeoEN->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $seoDimensionRepository->createClone($draftSeoDE->reveal(), 'new-resource-1')
            ->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeSeoNotFound(): void
    {
        $this->expectException(SeoNotFoundException::class);

        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateSeoMessage::class);
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

        $seoDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }

    public function testInvokeSeoNotFoundNotMandatory(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);

        $handler = new DuplicateSeoMessageHandler(
            $seoDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal()
        );

        $message = $this->prophesize(DuplicateSeoMessage::class);
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

        $seoDimensionRepository->findByDimensionIdentifiers(
            self::RESOURCE_KEY,
            'resource-1',
            [$draftDimensionIdentifierEN->reveal(), $draftDimensionIdentifierDE->reveal()]
        )->shouldBeCalled()->willReturn([]);

        $handler->__invoke($message->reveal());
    }
}
