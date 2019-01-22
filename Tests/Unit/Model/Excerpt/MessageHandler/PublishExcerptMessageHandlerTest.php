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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\PublishExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\PublishExcerptMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceRepositoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class PublishExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $iconReferenceRepository = $this->prophesize(IconReferenceRepositoryInterface::class);
        $imageReferenceRepository = $this->prophesize(ImageReferenceRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new PublishExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $iconReferenceRepository->reveal(),
            $imageReferenceRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(PublishExcerptMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $localizedLiveDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedLiveDimensionIdentifier->reveal());

        $localizedDraftExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedLiveExcerpt = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findDimension(self::RESOURCE_KEY, 'resource-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedDraftExcerpt);

        $excerptDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedLiveDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveExcerpt);

        $localizedLiveExcerpt->copyAttributesFrom($localizedDraftExcerpt->reveal())
            ->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        // category handling
        $liveCategory1 = $this->prophesize(CategoryInterface::class);
        $draftCategory1 = $this->prophesize(CategoryInterface::class);
        $draftCategory2 = $this->prophesize(CategoryInterface::class);

        $localizedLiveExcerpt->getCategories()->shouldBeCalled()->willReturn([$liveCategory1->reveal()]);
        $localizedLiveExcerpt->removeCategory($liveCategory1->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        $localizedDraftExcerpt->getCategories()->shouldBeCalled()->willReturn([$draftCategory1->reveal(), $draftCategory2->reveal()]);
        $localizedLiveExcerpt->addCategory($draftCategory1->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());
        $localizedLiveExcerpt->addCategory($draftCategory2->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        // tag handling
        $draftTag = $this->prophesize(TagInterface::class);
        $draftTagReference = $this->prophesize(TagReferenceInterface::class);
        $draftTagReference->getTag()->shouldBeCalled()->willReturn($draftTag->reveal());
        $draftTagReference->getOrder()->shouldBeCalled()->willReturn(4);
        $liveTagReference = $this->prophesize(TagReferenceInterface::class);

        $localizedLiveExcerpt->getTags()->shouldBeCalled()->willReturn([$liveTagReference->reveal()]);
        $localizedLiveExcerpt->removeTag($liveTagReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());
        $tagReferenceRepository->remove($liveTagReference->reveal())->shouldBeCalled();

        $localizedDraftExcerpt->getTags()->shouldBeCalled()->willReturn([$draftTagReference->reveal()]);
        $newLiveTagReference = $this->prophesize(TagReferenceInterface::class);
        $tagReferenceRepository->create($localizedLiveExcerpt->reveal(), $draftTag->reveal(), 4)
            ->shouldBeCalled()->willReturn($newLiveTagReference->reveal());
        $localizedLiveExcerpt->addTag($newLiveTagReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        // icon handling
        $draftIconMedia = $this->prophesize(MediaInterface::class);
        $draftIconReference = $this->prophesize(IconReferenceInterface::class);
        $draftIconReference->getMedia()->shouldBeCalled()->willReturn($draftIconMedia->reveal());
        $draftIconReference->getOrder()->shouldBeCalled()->willReturn(4);
        $liveIconReference = $this->prophesize(IconReferenceInterface::class);

        $localizedLiveExcerpt->getIcons()->shouldBeCalled()->willReturn([$liveIconReference->reveal()]);
        $localizedLiveExcerpt->removeIcon($liveIconReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());
        $iconReferenceRepository->remove($liveIconReference->reveal())->shouldBeCalled();

        $localizedDraftExcerpt->getIcons()->shouldBeCalled()->willReturn([$draftIconReference->reveal()]);
        $newLiveIconReference = $this->prophesize(IconReferenceInterface::class);
        $iconReferenceRepository->create($localizedLiveExcerpt->reveal(), $draftIconMedia->reveal(), 4)
            ->shouldBeCalled()->willReturn($newLiveIconReference->reveal());
        $localizedLiveExcerpt->addIcon($newLiveIconReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        // image handling
        $draftImageMedia = $this->prophesize(MediaInterface::class);
        $draftImageReference = $this->prophesize(ImageReferenceInterface::class);
        $draftImageReference->getMedia()->shouldBeCalled()->willReturn($draftImageMedia->reveal());
        $draftImageReference->getOrder()->shouldBeCalled()->willReturn(4);
        $liveImageReference = $this->prophesize(ImageReferenceInterface::class);

        $localizedLiveExcerpt->getImages()->shouldBeCalled()->willReturn([$liveImageReference->reveal()]);
        $localizedLiveExcerpt->removeImage($liveImageReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());
        $imageReferenceRepository->remove($liveImageReference->reveal())->shouldBeCalled();

        $localizedDraftExcerpt->getImages()->shouldBeCalled()->willReturn([$draftImageReference->reveal()]);
        $newLiveImageReference = $this->prophesize(ImageReferenceInterface::class);
        $imageReferenceRepository->create($localizedLiveExcerpt->reveal(), $draftImageMedia->reveal(), 4)
            ->shouldBeCalled()->willReturn($newLiveImageReference->reveal());
        $localizedLiveExcerpt->addImage($newLiveImageReference->reveal())->shouldBeCalled()->willReturn($localizedLiveExcerpt->reveal());

        $excerptView = $this->prophesize(ExcerptViewInterface::class);
        $excerptViewFactory->create([$localizedLiveExcerpt->reveal()], 'en')
            ->shouldBeCalled()->willReturn($excerptView->reveal());

        $message->setExcerpt($excerptView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeExcerptNotFound(): void
    {
        $this->expectException(ExcerptNotFoundException::class);

        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $iconReferenceRepository = $this->prophesize(IconReferenceRepositoryInterface::class);
        $imageReferenceRepository = $this->prophesize(ImageReferenceRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new PublishExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $iconReferenceRepository->reveal(),
            $imageReferenceRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(PublishExcerptMessage::class);
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getLocale()->shouldBeCalled()->willReturn('en');
        $message->isMandatory()->shouldBeCalled()->willReturn(true);

        $localizedDraftDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'en',
            ]
        )->shouldBeCalled()->willReturn($localizedDraftDimensionIdentifier->reveal());

        $excerptDimensionRepository->findDimension(self::RESOURCE_KEY, 'resource-1', $localizedDraftDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
