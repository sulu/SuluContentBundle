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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
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
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\ModifyExcerptMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
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
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $iconReferenceRepository = $this->prophesize(IconReferenceRepositoryInterface::class);
        $imageReferenceRepository = $this->prophesize(ImageReferenceRepositoryInterface::class);
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $tagRepository = $this->prophesize(TagRepositoryInterface::class);
        $mediaRepository = $this->prophesize(MediaRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new ModifyExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $iconReferenceRepository->reveal(),
            $imageReferenceRepository->reveal(),
            $categoryRepository->reveal(),
            $tagRepository->reveal(),
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
        $message->getCategoryIds()->shouldBeCalled()->willReturn([2]);
        $message->getTagNames()->shouldBeCalled()->willReturn(['tag-1']);
        $message->getIconMediaIds()->shouldBeCalled()->willReturn([1, 2]);
        $message->getImageMediaIds()->shouldBeCalled()->willReturn([1]);

        $localizedDimensionIdentifier = $this->prophesize(DimensionIdentifierInterface::class);
        $dimensionIdentifierRepository->findOrCreateByAttributes(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => 'de',
            ]
        )->shouldBeCalled()->willReturn($localizedDimensionIdentifier->reveal());

        $localizedExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedExcerpt->setTitle('title-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setMore('more-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setDescription('description-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        // category handling
        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->shouldBeCalled()->willReturn(1);
        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->shouldBeCalled()->willReturn(2);

        $localizedExcerpt->getCategory(2)->shouldBeCalled()->willReturn(null);
        $categoryRepository->findCategoryById(2)->shouldBeCalled()->willReturn($category2->reveal());
        $localizedExcerpt->addCategory($category2->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->getCategories()->shouldBeCalled()->willReturn([$category1->reveal(), $category2->reveal()]);
        $localizedExcerpt->removeCategory($category1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        // tag handling
        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getName()->shouldBeCalled()->willReturn('tag-1');
        $tagReference1 = $this->prophesize(TagReferenceInterface::class);
        $tagReference1->getTag()->shouldBeCalled()->willReturn($tag1->reveal());

        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getName()->shouldBeCalled()->willReturn('tag-2');
        $tagReference2 = $this->prophesize(TagReferenceInterface::class);
        $tagReference2->getTag()->shouldBeCalled()->willReturn($tag2->reveal());

        $localizedExcerpt->getTag('tag-1')->shouldBeCalled()->willReturn(null);
        $tagRepository->findTagByName('tag-1')->shouldBeCalled()->willReturn($tag1->reveal());
        $tagReferenceRepository->create($localizedExcerpt->reveal(), $tag1->reveal())
            ->shouldBeCalled()->willReturn($tagReference1->reveal());
        $localizedExcerpt->addTag($tagReference1->reveal())->shouldBeCalled();
        $tagReference1->setOrder(0)->shouldBeCalled()->willReturn($tagReference1->reveal());

        $localizedExcerpt->getTags()->shouldBeCalled()->willReturn([$tagReference1->reveal(), $tagReference2->reveal()]);
        $localizedExcerpt->removeTag($tagReference2->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $tagReferenceRepository->remove($tagReference2->reveal())->shouldBeCalled();

        // icon handling
        $media1 = $this->prophesize(MediaInterface::class);
        $media1->getId()->shouldBeCalled()->willReturn(1);
        $iconReference1 = $this->prophesize(IconReferenceInterface::class);
        $iconReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());

        $media2 = $this->prophesize(MediaInterface::class);
        $media2->getId()->shouldBeCalled()->willReturn(2);
        $iconReference2 = $this->prophesize(IconReferenceInterface::class);
        $iconReference2->getMedia()->shouldBeCalled()->willReturn($media2->reveal());

        $localizedExcerpt->getIcon(1)->shouldBeCalled()->willReturn(null);
        $mediaRepository->findMediaById(1)->shouldBeCalled()->willReturn($media1->reveal());
        $iconReferenceRepository->create($localizedExcerpt->reveal(), $media1->reveal())
            ->shouldBeCalled()->willReturn($iconReference1);
        $localizedExcerpt->addIcon($iconReference1->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $iconReference1->setOrder(0)->shouldBeCalled()->willReturn($iconReference1->reveal());

        $localizedExcerpt->getIcon(2)->shouldBeCalled()->willReturn($iconReference2->reveal());
        $iconReference2->setOrder(1)->shouldBeCalled()->willReturn($iconReference2->reveal());

        $localizedExcerpt->getIcons()->shouldBeCalled()->willReturn([$iconReference1->reveal(), $iconReference2->reveal()]);

        // image handling
        $imageReference1 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference1->getMedia()->shouldBeCalled()->willReturn($media1->reveal());

        $imageReference2 = $this->prophesize(ImageReferenceInterface::class);
        $imageReference2->getMedia()->shouldBeCalled()->willReturn($media2->reveal());

        $localizedExcerpt->getImage(1)->shouldBeCalled()->willReturn($imageReference1->reveal());
        $imageReference1->setOrder(0)->shouldBeCalled()->willReturn($imageReference1->reveal());

        $localizedExcerpt->getImages()->shouldBeCalled()->willReturn([$imageReference1->reveal(), $imageReference2->reveal()]);
        $localizedExcerpt->removeImage($imageReference2->reveal())->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $imageReferenceRepository->remove($imageReference2->reveal())->shouldBeCalled();

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
        $tagReferenceRepository = $this->prophesize(TagReferenceRepositoryInterface::class);
        $iconReferenceRepository = $this->prophesize(IconReferenceRepositoryInterface::class);
        $imageReferenceRepository = $this->prophesize(ImageReferenceRepositoryInterface::class);
        $categoryRepository = $this->prophesize(CategoryRepositoryInterface::class);
        $tagRepository = $this->prophesize(TagRepositoryInterface::class);
        $mediaRepository = $this->prophesize(MediaRepositoryInterface::class);
        $excerptViewFactory = $this->prophesize(ExcerptViewFactoryInterface::class);

        $handler = new ModifyExcerptMessageHandler(
            $excerptDimensionRepository->reveal(),
            $dimensionIdentifierRepository->reveal(),
            $tagReferenceRepository->reveal(),
            $iconReferenceRepository->reveal(),
            $imageReferenceRepository->reveal(),
            $categoryRepository->reveal(),
            $tagRepository->reveal(),
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
        $message->getTagNames()->shouldBeCalled()->willReturn([]);
        $message->getIconMediaIds()->shouldBeCalled()->willReturn([]);
        $message->getImageMediaIds()->shouldBeCalled()->willReturn([]);

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

        $localizedExcerpt = $this->prophesize(ExcerptDimensionInterface::class);
        $localizedExcerpt->setTitle('title-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setMore('more-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());
        $localizedExcerpt->setDescription('description-1')->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $localizedExcerpt->getCategories()->shouldBeCalled()->willReturn([]);
        $localizedExcerpt->getTags()->shouldBeCalled()->willReturn([]);
        $localizedExcerpt->getIcons()->shouldBeCalled()->willReturn([]);
        $localizedExcerpt->getImages()->shouldBeCalled()->willReturn([]);

        $excerptDimensionRepository->findOrCreateDimension(self::RESOURCE_KEY, 'resource-1', $localizedDimensionIdentifier->reveal())
            ->shouldBeCalled()->willReturn($localizedExcerpt->reveal());

        $excerptViewFactory->create([$localizedExcerpt->reveal()], 'de')
            ->shouldBeCalled()->willReturn(null);

        $handler->__invoke($message->reveal());
    }
}
