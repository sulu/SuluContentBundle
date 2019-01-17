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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler;

use Doctrine\ORM\EntityNotFoundException;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;

class ModifyExcerptMessageHandler
{
    /**
     * @var ExcerptDimensionRepositoryInterface
     */
    private $excerptDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @var TagRepositoryInterface
     */
    private $tagRepository;

    /**
     * @var MediaRepositoryInterface
     */
    private $mediaRepository;

    /**
     * @var ExcerptViewFactoryInterface
     */
    private $excerptViewFactory;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        MediaRepositoryInterface $mediaRepository,
        ExcerptViewFactoryInterface $excerptViewFactory
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->mediaRepository = $mediaRepository;
        $this->excerptViewFactory = $excerptViewFactory;
    }

    public function __invoke(ModifyExcerptMessage $message): void
    {
        $localizedDraftExcerpt = $this->findOrCreateDraftExcerptDimension(
            $message->getResourceKey(),
            $message->getResourceId(),
            $message->getLocale()
        );
        $this->setData($message, $localizedDraftExcerpt);

        $excerptView = $this->excerptViewFactory->create([$localizedDraftExcerpt], $message->getLocale());
        if (!$excerptView) {
            throw new ExcerptNotFoundException($message->getResourceKey(), $message->getResourceId());
        }

        $message->setExcerpt($excerptView);
    }

    private function setData(
        ModifyExcerptMessage $message,
        ExcerptDimensionInterface $localizedDraftExcerpt
    ): void {
        $localizedDraftExcerpt->setTitle($message->getTitle());
        $localizedDraftExcerpt->setMore($message->getMore());
        $localizedDraftExcerpt->setDescription($message->getDescription());

        $localizedDraftExcerpt->clearCategories();
        foreach ($message->getCategoryIds() as $categoryId) {
            $category = $this->categoryRepository->findCategoryById($categoryId);
            if (!$category) {
                throw EntityNotFoundException::fromClassNameAndIdentifier(
                    Category::class,
                    ['id' => $categoryId]
                );
            }

            $localizedDraftExcerpt->addCategory($category);
        }

        $localizedDraftExcerpt->clearTags();
        foreach ($message->getTagNames() as $tagName) {
            $tag = $this->tagRepository->findTagByName($tagName);
            if (!$tag) {
                throw EntityNotFoundException::fromClassNameAndIdentifier(
                    Tag::class,
                    ['name' => $tagName]
                );
            }

            $localizedDraftExcerpt->addTag($tag);
        }

        foreach ($message->getIconMediaIds() as $iconMediaId) {
            /** @var ?Media */
            $media = $this->mediaRepository->findMediaById($iconMediaId);
            if (!$media) {
                throw EntityNotFoundException::fromClassNameAndIdentifier(
                    Tag::class,
                    ['id' => $iconMediaId]
                );
            }

            $localizedDraftExcerpt->addIcon($media);
        }

        foreach ($message->getImageMediaIds() as $imageMediaId) {
            /** @var ?Media */
            $media = $this->mediaRepository->findMediaById($imageMediaId);
            if (!$media) {
                throw EntityNotFoundException::fromClassNameAndIdentifier(
                    Tag::class,
                    ['id' => $imageMediaId]
                );
            }

            $localizedDraftExcerpt->addImage($media);
        }
    }

    private function findOrCreateDraftExcerptDimension(
        string $resourceKey,
        string $resourceId,
        string $locale
    ): ExcerptDimensionInterface {
        $dimensionIdentifier = $this->getDraftDimensionIdentifier($locale);

        return $this->excerptDimensionRepository->findOrCreateDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    private function getDraftDimensionIdentifier(string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
