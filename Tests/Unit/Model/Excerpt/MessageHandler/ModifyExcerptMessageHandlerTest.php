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
use Prophecy\Argument;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptViewInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\ModifyExcerptMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceRepositoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;

class ModifyExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $tagRepository = $this->prophesize(TagRepositoryInterface::class);
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $mediaRepository = $this->prophesize(MediaRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new ModifyExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $categoryRepository->reveal(),
            $tagRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $mediaRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyExcerptMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getTitle()->shouldBeCalled()->willReturn('title-1');
        $message->getMore()->shouldBeCalled()->willReturn('more-1');
        $message->getDescription()->shouldBeCalled()->willReturn('description-1');
        $message->getCategoryIds()->shouldBeCalled()->willReturn([]);
        $message->getTagNames()->shouldBeCalled()->willReturn(['tag-1']);
        $message->getIconMediaIds()->shouldBeCalled()->willReturn([1, 2]);
        $message->getImageMediaIds()->shouldBeCalled()->willReturn([3]);

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $tag1 = $this->prophesize(TagInterface::class);
        $media1 = $this->prophesize(MediaInterface::class);
        $media2 = $this->prophesize(MediaInterface::class);
        $media3 = $this->prophesize(MediaInterface::class);

        $categoryRepository->findCategoryById(Argument::any())->shouldNotBeCalled();
        $tagRepository->findTagByName('tag-1')->shouldBeCalled()->willReturn($tag1);
        $mediaRepository->findMediaById(1)->shouldBeCalled()->willReturn($media1);
        $mediaRepository->findMediaById(2)->shouldBeCalled()->willReturn($media2);
        $mediaRepository->findMediaById(3)->shouldBeCalled()->willReturn($media3);

        $localizedExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedExcerpt->setTitle('title-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setMore('more-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setDescription('description-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearCategories()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addCategory(Argument::any())->shouldNotBeCalled();

        $localizedExcerpt->clearTags()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addTag($tag1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearIcons()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addIcon($media1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addIcon($media2->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearImages()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addImage($media3->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptView = $this->prophesize(ExcerptViewInterface::class);
        $excerptViewFactory->create([$localizedExcerpt->reveal()], 'de')
            ->shouldBeCalled()->willReturn($excerptView->reveal());

        $message->setExcerpt($excerptView->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }

    public function testInvokeExcerptNotFound(): void
    {
        $this->expectException(ExcerptNotFoundException::class);

        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);
        $dimensionIdentifierRepository = $this->prophesize(DimensionIdentifierRepositoryInterface::class);
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $tagRepository = $this->prophesize(TagRepositoryInterface::class);
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $mediaRepository = $this->prophesize(MediaRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new ModifyExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $categoryRepository->reveal(),
            $tagRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $mediaRepository->reveal(),
            $excerptViewFactory->reveal()
        );

        $message = $this->prophesize(ModifyExcerptMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);
        $message->getLocale()->shouldBeCalled()->willReturn('de');
        $message->getTitle()->shouldBeCalled()->willReturn('title-1');
        $message->getMore()->shouldBeCalled()->willReturn('more-1');
        $message->getDescription()->shouldBeCalled()->willReturn('description-1');
        $message->getCategoryIds()->shouldBeCalled()->willReturn([]);
        $message->getTagNames()->shouldBeCalled()->willReturn(['tag-1']);
        $message->getIconMediaIds()->shouldBeCalled()->willReturn([1, 2]);
        $message->getImageMediaIds()->shouldBeCalled()->willReturn([3]);

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $tag1 = $this->prophesize(TagInterface::class);
        $media1 = $this->prophesize(MediaInterface::class);
        $media2 = $this->prophesize(MediaInterface::class);
        $media3 = $this->prophesize(MediaInterface::class);

        $categoryRepository->findCategoryById(Argument::any())->shouldNotBeCalled();
        $tagRepository->findTagByName('tag-1')->shouldBeCalled()->willReturn($tag1);
        $mediaRepository->findMediaById(1)->shouldBeCalled()->willReturn($media1);
        $mediaRepository->findMediaById(2)->shouldBeCalled()->willReturn($media2);
        $mediaRepository->findMediaById(3)->shouldBeCalled()->willReturn($media3);

        $localizedExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedExcerpt->setTitle('title-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setMore('more-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setDescription('description-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearCategories()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addCategory(Argument::any())->shouldNotBeCalled();

        $localizedExcerpt->clearTags()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addTag($tag1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearIcons()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addIcon($media1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addIcon($media2->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->clearImages()->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->addImage($media3->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptViewFactory->create([$localizedExcerpt->reveal()], 'de')
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
