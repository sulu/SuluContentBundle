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
        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getName()->shouldBeCalled()->willReturn('tag-1');
        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getName()->shouldBeCalled()->willReturn('tag-2');
        $tag3 = $this->prophesize(TagInterface::class);
        $tag3->getName()->shouldBeCalled()->willReturn('tag-3');

        $draftTag2Reference = $this->prophesize(TagReferenceInterface::class);
        $draftTag2Reference->getTag()->shouldBeCalled()->willReturn($tag2->reveal());
        $draftTag2Reference->getOrder()->shouldBeCalled()->willReturn(1);
        $draftTag3Reference = $this->prophesize(TagReferenceInterface::class);
        $draftTag3Reference->getTag()->shouldBeCalled()->willReturn($tag3->reveal());
        $draftTag3Reference->getOrder()->shouldBeCalled()->willReturn(2);

        $liveTag1Reference = $this->prophesize(TagReferenceInterface::class);
        $liveTag1Reference->getTag()->shouldBeCalled()->willReturn($tag1->reveal());
        $liveTag2Reference = $this->prophesize(TagReferenceInterface::class);
        $liveTag2Reference->getTag()->shouldBeCalled()->willReturn($tag2->reveal());
        $createdTag3Reference = $this->prophesize(TagReferenceInterface::class);
        $createdTag3Reference->getTag()->shouldBeCalled()->willReturn($tag3->reveal());

        $localizedDraftExcerpt->getTags()->shouldBeCalled()->willReturn(
            [$draftTag2Reference->reveal(), $draftTag3Reference->reveal()]
        );

        $localizedLiveExcerpt->getTag('tag-2')->shouldBeCalled()->willReturn($liveTag2Reference->reveal());
        $liveTag2Reference->setOrder(1)->shouldBeCalled();

        $localizedLiveExcerpt->getTag('tag-3')->shouldBeCalled()->willReturn(null);
        $tagReferenceRepository->create($localizedLiveExcerpt->reveal(), $tag3->reveal(), 2)->shouldBeCalled()->willReturn($createdTag3Reference->reveal());
        $localizedLiveExcerpt->addTag($createdTag3Reference->reveal())->shouldBeCalled();
        $createdTag3Reference->setOrder(2)->shouldBeCalled();

        $localizedLiveExcerpt->getTags()->shouldBeCalled()->willReturn(
            [$liveTag1Reference->reveal(), $liveTag2Reference->reveal(), $createdTag3Reference->reveal()]
        );
        $localizedLiveExcerpt->removeTag($liveTag1Reference->reveal())->shouldBeCalled();
        $tagReferenceRepository->remove($liveTag1Reference->reveal())->shouldBeCalled();

        // icon handling
        $media1 = $this->prophesize(MediaInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);
        $media2 = $this->prophesize(MediaInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);
        $media3 = $this->prophesize(MediaInterface::class);
        $media3->getId()->shouldBeCalled()->willReturn(3);

        $draftIcon2Reference = $this->prophesize(IconReferenceInterface::class);
        $draftIcon2Reference->getMedia()->shouldBeCalled()->willReturn($media2->reveal());
        $draftIcon2Reference->getOrder()->shouldBeCalled()->willReturn(1);
        $draftIcon3Reference = $this->prophesize(IconReferenceInterface::class);
        $draftIcon3Reference->getMedia()->shouldBeCalled()->willReturn($media3->reveal());
        $draftIcon3Reference->getOrder()->shouldBeCalled()->willReturn(2);

        $liveIcon1Reference = $this->prophesize(IconReferenceInterface::class);
        $liveIcon1Reference->getMedia()->shouldBeCalled()->willReturn($media1->reveal());
        $liveIcon2Reference = $this->prophesize(IconReferenceInterface::class);
        $liveIcon2Reference->getMedia()->shouldBeCalled()->willReturn($media2->reveal());
        $createdIcon3Reference = $this->prophesize(IconReferenceInterface::class);
        $createdIcon3Reference->getMedia()->shouldBeCalled()->willReturn($media3->reveal());

        $localizedDraftExcerpt->getIcons()->shouldBeCalled()->willReturn(
            [$draftIcon2Reference->reveal(), $draftIcon3Reference->reveal()]
        );

        $localizedLiveExcerpt->getIcon(2)->shouldBeCalled()->willReturn($liveIcon2Reference);
        $liveIcon2Reference->setOrder(1)->shouldBeCalled();

        $localizedLiveExcerpt->getIcon(3)->shouldBeCalled()->willReturn(null);
        $iconReferenceRepository->create($localizedLiveExcerpt->reveal(), $media3->reveal(), 2)->shouldBeCalled()->willReturn($createdIcon3Reference->reveal());
        $localizedLiveExcerpt->addIcon($createdIcon3Reference->reveal())->shouldBeCalled();
        $createdIcon3Reference->setOrder(2)->shouldBeCalled();

        $localizedLiveExcerpt->getIcons()->shouldBeCalled()->willReturn(
            [$liveIcon1Reference->reveal(), $liveIcon2Reference->reveal(), $createdIcon3Reference->reveal()]
        );
        $localizedLiveExcerpt->removeIcon($liveIcon1Reference->reveal())->shouldBeCalled();
        $iconReferenceRepository->remove($liveIcon1Reference->reveal())->shouldBeCalled();

        // image handling
        $draftImage2Reference = $this->prophesize(ImageReferenceInterface::class);
        $draftImage2Reference->getMedia()->shouldBeCalled()->willReturn($media2->reveal());
        $draftImage2Reference->getOrder()->shouldBeCalled()->willReturn(1);
        $draftImage3Reference = $this->prophesize(ImageReferenceInterface::class);
        $draftImage3Reference->getMedia()->shouldBeCalled()->willReturn($media3->reveal());
        $draftImage3Reference->getOrder()->shouldBeCalled()->willReturn(2);

        $liveImage1Reference = $this->prophesize(ImageReferenceInterface::class);
        $liveImage1Reference->getMedia()->shouldBeCalled()->willReturn($media1->reveal());
        $liveImage2Reference = $this->prophesize(ImageReferenceInterface::class);
        $liveImage2Reference->getMedia()->shouldBeCalled()->willReturn($media2->reveal());
        $createdImage3Reference = $this->prophesize(ImageReferenceInterface::class);
        $createdImage3Reference->getMedia()->shouldBeCalled()->willReturn($media3->reveal());

        $localizedDraftExcerpt->getImages()->shouldBeCalled()->willReturn(
            [$draftImage2Reference->reveal(), $draftImage3Reference->reveal()]
        );

        $localizedLiveExcerpt->getImage(2)->shouldBeCalled()->willReturn($liveImage2Reference);
        $liveImage2Reference->setOrder(1)->shouldBeCalled();

        $localizedLiveExcerpt->getImage(3)->shouldBeCalled()->willReturn(null);
        $imageReferenceRepository->create($localizedLiveExcerpt->reveal(), $media3->reveal(), 2)->shouldBeCalled()->willReturn($createdImage3Reference->reveal());
        $localizedLiveExcerpt->addImage($createdImage3Reference->reveal())->shouldBeCalled();
        $createdImage3Reference->setOrder(2)->shouldBeCalled();

        $localizedLiveExcerpt->getImages()->shouldBeCalled()->willReturn(
            [$liveImage1Reference->reveal(), $liveImage2Reference->reveal(), $createdImage3Reference->reveal()]
        );
        $localizedLiveExcerpt->removeImage($liveImage1Reference->reveal())->shouldBeCalled();
        $imageReferenceRepository->remove($liveImage1Reference->reveal())->shouldBeCalled();

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
