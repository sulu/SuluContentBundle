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
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\ModifyContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ModifyContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new ModifyContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $factory->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getType()->shouldBeCalled()->willReturn('default');
        $message->getData()->shouldBeCalled()->willReturn(['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']);

        $draftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimensionIdentifier->reveal());
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $metadata = $this->prophesize(StructureMetadata::class);
        $titleProperty = $this->prophesize(PropertyMetadata::class);
        $titleProperty->getName()->shouldBeCalled()->willReturn('title');
        $titleProperty->isLocalized()->shouldBeCalled()->willReturn(true);
        $articleProperty = $this->prophesize(PropertyMetadata::class);
        $articleProperty->getName()->shouldBeCalled()->willReturn('article');
        $articleProperty->isLocalized()->shouldBeCalled()->willReturn(false);
        $metadata->getProperties()->shouldBeCalled()->willReturn([$titleProperty->reveal(), $articleProperty->reveal()]);
        $factory->getStructureMetadata(self::RESOURCE_KEY, 'default')->shouldBeCalled()->willReturn($metadata->reveal());

        $draftContent = $this->prophesize(ContentDimensionInterface::class);
        $draftContent->setType('default')->shouldBeCalled()->willReturn($draftContent->reveal());
        $draftContent->setData(['article' => '<p>Sulu is awesome</p>'])
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $localizedContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedContent->setType('default')->shouldBeCalled()->willReturn($localizedContent->reveal());
        $localizedContent->setData(['title' => 'Sulu'])
            ->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $draftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentViewFactory->create([$localizedContent->reveal(), $draftContent->reveal()], 'de')
            ->shouldBeCalled()->willReturn($contentView->reveal());

        $message->setContent($contentView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeContentNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new ModifyContentMessageHandler(
            $contentDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $factory->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getType()->shouldBeCalled()->willReturn('default');
        $message->getData()->shouldBeCalled()->willReturn(['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']);

        $draftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimensionIdentifier->reveal());
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $metadata = $this->prophesize(StructureMetadata::class);
        $titleProperty = $this->prophesize(PropertyMetadata::class);
        $titleProperty->getName()->shouldBeCalled()->willReturn('title');
        $titleProperty->isLocalized()->shouldBeCalled()->willReturn(true);
        $articleProperty = $this->prophesize(PropertyMetadata::class);
        $articleProperty->getName()->shouldBeCalled()->willReturn('article');
        $articleProperty->isLocalized()->shouldBeCalled()->willReturn(false);
        $metadata->getProperties()->shouldBeCalled()->willReturn([$titleProperty->reveal(), $articleProperty->reveal()]);
        $factory->getStructureMetadata(self::RESOURCE_KEY, 'default')->shouldBeCalled()->willReturn($metadata->reveal());

        $draftContent = $this->prophesize(ContentDimensionInterface::class);
        $draftContent->setType('default')->shouldBeCalled()->willReturn($draftContent->reveal());
        $draftContent->setData(['article' => '<p>Sulu is awesome</p>'])
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $localizedContent = $this->prophesize(ContentDimensionInterface::class);
        $localizedContent->setType('default')->shouldBeCalled()->willReturn($localizedContent->reveal());
        $localizedContent->setData(['title' => 'Sulu'])
            ->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $draftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $contentDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentViewFactory->create([$localizedContent->reveal(), $draftContent->reveal()], 'de')
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
