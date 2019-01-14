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
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ModifyContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testInvoke(): void
    {
        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new ModifyContentMessageHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $factory->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getType()->shouldBeCalled()->willReturn('default');
        $message->getData()->shouldBeCalled()->willReturn(['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']);

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

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

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $draftDimension->reveal())
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $localizedDimension->reveal())
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

        $contentRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new ModifyContentMessageHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $factory->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('product-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getType()->shouldBeCalled()->willReturn('default');
        $message->getData()->shouldBeCalled()->willReturn(['title' => 'Sulu', 'article' => '<p>Sulu is awesome</p>']);

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $localizedDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->shouldBeCalled()->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimension->reveal());

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

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $draftDimension->reveal())
            ->shouldBeCalled()->willReturn($draftContent->reveal());

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $localizedDimension->reveal())
            ->shouldBeCalled()->willReturn($localizedContent->reveal());

        $contentViewFactory->create([$localizedContent->reveal(), $draftContent->reveal()], 'de')
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
