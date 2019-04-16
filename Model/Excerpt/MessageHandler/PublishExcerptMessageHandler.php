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

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\PublishExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceRepositoryInterface;

class PublishExcerptMessageHandler
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
     * @var ImageReferenceRepositoryInterface
     */
    private $imageReferenceRepository;

    /**
     * @var ExcerptViewFactoryInterface
     */
    private $excerptViewFactory;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        TagReferenceRepositoryInterface $tagReferenceRepository,
        IconReferenceRepositoryInterface $iconReferenceRepository,
        ImageReferenceRepositoryInterface $imageReferenceRepository,
        ExcerptViewFactoryInterface $excerptViewFactory
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->tagReferenceRepository = $tagReferenceRepository;
        $this->iconReferenceRepository = $iconReferenceRepository;
        $this->imageReferenceRepository = $imageReferenceRepository;
        $this->excerptViewFactory = $excerptViewFactory;
    }

    public function __invoke(PublishExcerptMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();
        $mandatory = $message->isMandatory();

        $publishedExcerptDimensions = array_filter([
            $this->publishExcerptDimensions($resourceKey, $resourceId, $mandatory, $message->getLocale()),
        ]);

        if (!$publishedExcerptDimensions) {
            return;
        }

        $excerptView = $this->excerptViewFactory->create($publishedExcerptDimensions, $message->getLocale());
        if (!$excerptView) {
            throw new ExcerptNotFoundException(['resourceKey' => $resourceKey, 'resourceId' => $resourceId]);
        }

        $message->setExcerpt($excerptView);
    }

    protected function publishExcerptDimensions(
        string $resourceKey,
        string $resourceId,
        bool $mandatory,
        string $locale
    ): ?ExcerptDimensionInterface {
        $draftDimensionIdentifier = $this->getDimensionIdentifier(DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT, $locale);
        $draftExcerpt = $this->excerptDimensionRepository->findDimension($resourceKey, $resourceId, $draftDimensionIdentifier);

        if (!$draftExcerpt) {
            if (!$mandatory) {
                return null;
            }

            throw new ExcerptNotFoundException(['resourceKey' => $resourceKey, 'resourceId' => $resourceId]);
        }

        $liveDimensionIdentifier = $this->getDimensionIdentifier(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveExcerpt = $this->excerptDimensionRepository->findOrCreateDimension($resourceKey, $resourceId, $liveDimensionIdentifier);

        $liveExcerpt->copyAttributesFrom($draftExcerpt);
        $this->copyCategories($draftExcerpt, $liveExcerpt);
        $this->copyTags($draftExcerpt, $liveExcerpt);
        $this->copyIcons($draftExcerpt, $liveExcerpt);
        $this->copyImages($draftExcerpt, $liveExcerpt);

        return $liveExcerpt;
    }

    protected function getDimensionIdentifier(string $stage, string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = $stage;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }

    private function copyCategories(ExcerptDimensionInterface $draftExcerpt, ExcerptDimensionInterface $liveExcerpt): void
    {
        foreach ($liveExcerpt->getCategories() as $oldLiveCategory) {
            $liveExcerpt->removeCategory($oldLiveCategory);
        }

        foreach ($draftExcerpt->getCategories() as $draftCategory) {
            $liveExcerpt->addCategory($draftCategory);
        }
    }

    private function copyTags(ExcerptDimensionInterface $draftExcerpt, ExcerptDimensionInterface $liveExcerpt): void
    {
        $processedTagNames = [];
        foreach ($draftExcerpt->getTags() as $draftTagReference) {
            $liveTagReference = $liveExcerpt->getTag($draftTagReference->getTag()->getName());
            if (!$liveTagReference) {
                $liveTagReference = $this->tagReferenceRepository->create(
                    $liveExcerpt, $draftTagReference->getTag(), $draftTagReference->getOrder()
                );
                $liveExcerpt->addTag($liveTagReference);
            }

            $liveTagReference->setOrder($draftTagReference->getOrder());
            $processedTagNames[] = $liveTagReference->getTag()->getName();
        }

        foreach ($liveExcerpt->getTags() as $liveTagReference) {
            if (!\in_array($liveTagReference->getTag()->getName(), $processedTagNames, true)) {
                $liveExcerpt->removeTag($liveTagReference);
                $this->tagReferenceRepository->remove($liveTagReference);
            }
        }
    }

    private function copyIcons(ExcerptDimensionInterface $draftExcerpt, ExcerptDimensionInterface $liveExcerpt): void
    {
        $processedMediaIds = [];
        foreach ($draftExcerpt->getIcons() as $draftIconReference) {
            $liveIconReference = $liveExcerpt->getIcon($draftIconReference->getMedia()->getId());
            if (!$liveIconReference) {
                $liveIconReference = $this->iconReferenceRepository->create(
                    $liveExcerpt, $draftIconReference->getMedia(), $draftIconReference->getOrder()
                );
                $liveExcerpt->addIcon($liveIconReference);
            }

            $liveIconReference->setOrder($draftIconReference->getOrder());
            $processedMediaIds[] = $liveIconReference->getMedia()->getId();
        }

        foreach ($liveExcerpt->getIcons() as $liveIconReference) {
            if (!\in_array($liveIconReference->getMedia()->getId(), $processedMediaIds, true)) {
                $liveExcerpt->removeIcon($liveIconReference);
                $this->iconReferenceRepository->remove($liveIconReference);
            }
        }
    }

    private function copyImages(ExcerptDimensionInterface $draftExcerpt, ExcerptDimensionInterface $liveExcerpt): void
    {
        $processedMediaIds = [];
        foreach ($draftExcerpt->getImages() as $draftImageReference) {
            $liveImageReference = $liveExcerpt->getImage($draftImageReference->getMedia()->getId());
            if (!$liveImageReference) {
                $liveImageReference = $this->imageReferenceRepository->create(
                    $liveExcerpt, $draftImageReference->getMedia(), $draftImageReference->getOrder()
                );
                $liveExcerpt->addImage($liveImageReference);
            }

            $liveImageReference->setOrder($draftImageReference->getOrder());
            $processedMediaIds[] = $liveImageReference->getMedia()->getId();
        }

        foreach ($liveExcerpt->getImages() as $liveImageReference) {
            if (!\in_array($liveImageReference->getMedia()->getId(), $processedMediaIds, true)) {
                $liveExcerpt->removeImage($liveImageReference);
                $this->imageReferenceRepository->remove($liveImageReference);
            }
        }
    }
}
