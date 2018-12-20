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
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\PublishContentMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;

class PublishContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'products';

    public function testInvoke(): void
    {
        $contentRepository = $this->prophesize(ContentRepositoryInterface::class);
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $contentViewFactory = $this->prophesize(ContentViewFactoryInterface::class);

        $handler = new PublishContentMessageHandler(
            $contentRepository->reveal(),
            $dimensionRepository->reveal(),
            $contentViewFactory->reveal()
        );

        $message = $this->prophesize(PublishContentMessage::class);
        $message->getResourceKey()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->willReturn('product-1');
        $message->getLocale()->willReturn('en');
        $message->isMandatory()->willReturn(true);

        $draftDimension = $this->prophesize(DimensionInterface::class);
        $liveDimension = $this->prophesize(DimensionInterface::class);

        $localizedDraftDimension = $this->prophesize(DimensionInterface::class);
        $localizedLiveDimension = $this->prophesize(DimensionInterface::class);

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT]
        )->willReturn($draftDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->willReturn($localizedDraftDimension->reveal());

        $dimensionRepository->findOrCreateByAttributes(
            [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_LIVE]
        )->willReturn($liveDimension->reveal());
        $dimensionRepository->findOrCreateByAttributes(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->willReturn($localizedLiveDimension->reveal());

        $draftContent = $this->prophesize(ContentInterface::class);
        $draftContent->getType()->willReturn('default');
        $draftContent->getData()->willReturn(['article' => '<p>Sulu is awesome</p>']);
        $draftContent->getDimension()->willReturn($draftDimension->reveal());

        $liveContent = $this->prophesize(ContentInterface::class);
        $liveContent->setType('default')->shouldBeCalled();
        $liveContent->setData(['article' => '<p>Sulu is awesome</p>'])->shouldBeCalled();
        $draftContent->getDimension()->willReturn($liveDimension->reveal());

        $localizedDraftContent = $this->prophesize(ContentInterface::class);
        $localizedDraftContent->getType()->willReturn('default');
        $localizedDraftContent->getData()->willReturn(['title' => 'Sulu']);

        $localizedLiveContent = $this->prophesize(ContentInterface::class);
        $localizedLiveContent->setType('default')->shouldBeCalled();
        $localizedLiveContent->setData(['title' => 'Sulu'])->shouldBeCalled();

        $contentRepository->findByResource(self::RESOURCE_KEY, 'product-1', $draftDimension->reveal())
            ->willReturn($draftContent);

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $liveDimension->reveal())
            ->willReturn($liveContent);

        $contentRepository->findByResource(self::RESOURCE_KEY, 'product-1', $localizedDraftDimension->reveal())
            ->willReturn($localizedDraftContent);

        $contentRepository->findOrCreate(self::RESOURCE_KEY, 'product-1', $localizedLiveDimension->reveal())
            ->willReturn($localizedLiveContent);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentViewFactory->create([$liveContent->reveal(), $localizedLiveContent->reveal()], 'en')
            ->willReturn($contentView->reveal());

        $message->setContent(
            Argument::that(
                function ($result) use ($contentView) {
                    return $contentView->reveal() === $result;
                }
            )
        )->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
