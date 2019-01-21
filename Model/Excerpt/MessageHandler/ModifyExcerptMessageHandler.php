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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\ModifyExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceRepositoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
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
     * @var TagReferenceRepositoryInterface
     */
    private $tagReferenceRepository;

    /**
     * @var IconReferenceRepositoryInterface
     */
    private $iconReferenceRepository;

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
        TagReferenceRepositoryInterface $tagReferenceRepository,
        IconReferenceRepositoryInterface $iconReferenceRepository,
        CategoryRepositoryInterface $categoryRepository,
        TagRepositoryInterface $tagRepository,
        MediaRepositoryInterface $mediaRepository,
        ExcerptViewFactoryInterface $excerptViewFactory
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->tagReferenceRepository = $tagReferenceRepository;
        $this->iconReferenceRepository = $iconReferenceRepository;
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

        $this->updateCategories($message, $localizedDraftExcerpt);
        $this->updateTags($message, $localizedDraftExcerpt);
        $this->updateIcons($message, $localizedDraftExcerpt);
        $this->updateImages($message, $localizedDraftExcerpt);
    }

    private function updateCategories(ModifyExcerptMessage $message, ExcerptDimensionInterface $localizedDraftExcerpt): void
    {
        foreach ($message->getCategoryIds() as $categoryId) {
            $messageCategory = $localizedDraftExcerpt->getCategory($categoryId);
            if (!$messageCategory) {
                $messageCategory = $this->findCategoryById($categoryId);
                $localizedDraftExcerpt->addCategory($messageCategory);
            }
        }

        foreach ($localizedDraftExcerpt->getCategories() as $persistedCategory) {
            if (!in_array($persistedCategory->getId(), $message->getCategoryIds(), true)) {
                $localizedDraftExcerpt->removeCategory($persistedCategory);
            }
        }
    }

    private function updateTags(ModifyExcerptMessage $message, ExcerptDimensionInterface $localizedDraftExcerpt): void
    {
        foreach ($message->getTagNames() as $index => $tagName) {
            $messageTagReference = $localizedDraftExcerpt->getTag($tagName);
            if (!$messageTagReference) {
                $messageTag = $this->findTagByName($tagName);
                $messageTagReference = $this->tagReferenceRepository->create($localizedDraftExcerpt, $messageTag);
                $localizedDraftExcerpt->addTag($messageTagReference);
            }
            $messageTagReference->setOrder($index);
        }

        foreach ($localizedDraftExcerpt->getTags() as $persistedTagReference) {
            if (!in_array($persistedTagReference->getTag()->getName(), $message->getTagNames(), true)) {
                $localizedDraftExcerpt->removeTag($persistedTagReference);
                $this->tagReferenceRepository->remove($persistedTagReference);
            }
        }
    }

    private function updateIcons(ModifyExcerptMessage $message, ExcerptDimensionInterface $localizedDraftExcerpt): void
    {
        foreach ($message->getIconMediaIds() as $index => $iconMediaId) {
            $messageIconReference = $localizedDraftExcerpt->getIcon($iconMediaId);
            if (!$messageIconReference) {
                $messageIconMedia = $this->findMediaById($iconMediaId);
                $messageIconReference = $this->iconReferenceRepository->create($localizedDraftExcerpt, $messageIconMedia);
                $localizedDraftExcerpt->addIcon($messageIconReference);
            }
            $messageIconReference->setOrder($index);
        }

        foreach ($localizedDraftExcerpt->getIcons() as $persistedIconReference) {
            if (!in_array($persistedIconReference->getMedia()->getId(), $message->getIconMediaIds(), true)) {
                $localizedDraftExcerpt->removeIcon($persistedIconReference);
                $this->iconReferenceRepository->remove($persistedIconReference);
            }
        }
    }

    private function updateImages(ModifyExcerptMessage $message, ExcerptDimensionInterface $localizedDraftExcerpt): void
    {
        foreach ($message->getImageMediaIds() as $imageMediaId) {
            $messageImageMedia = $localizedDraftExcerpt->getImage($imageMediaId);
            if (!$messageImageMedia) {
                $messageImageMedia = $this->findMediaById($imageMediaId);
                $localizedDraftExcerpt->addImage($messageImageMedia);
            }
        }

        foreach ($localizedDraftExcerpt->getImages() as $persistedImageMedia) {
            if (!in_array($persistedImageMedia->getId(), $message->getImageMediaIds(), true)) {
                $localizedDraftExcerpt->removeImage($persistedImageMedia);
            }
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

    private function findTagByName(string $tagName): TagInterface
    {
        $tag = $this->tagRepository->findTagByName($tagName);
        if (!$tag) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(
                TagInterface::class,
                ['name' => $tagName]
            );
        }

        return $tag;
    }

    private function findCategoryById(int $categoryId): CategoryInterface
    {
        $category = $this->categoryRepository->findCategoryById($categoryId);
        if (!$category) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(
                CategoryInterface::class,
                ['id' => (string) $categoryId]
            );
        }

        return $category;
    }

    private function findMediaById(int $mediaId): MediaInterface
    {
        /** @var ?MediaInterface */
        $media = $this->mediaRepository->findMediaById($mediaId);
        if (!$media) {
            throw EntityNotFoundException::fromClassNameAndIdentifier(
                MediaInterface::class,
                ['id' => (string) $mediaId]
            );
        }

        return $media;
    }
}
