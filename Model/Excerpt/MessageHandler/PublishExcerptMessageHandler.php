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
     * @var ExcerptViewFactoryInterface
     */
    private $excerptViewFactory;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        TagReferenceRepositoryInterface $tagReferenceRepository,
        ExcerptViewFactoryInterface $excerptViewFactory
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->tagReferenceRepository = $tagReferenceRepository;
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
            throw new ExcerptNotFoundException($resourceKey, $resourceId);
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

            throw new ExcerptNotFoundException($resourceKey, $resourceId);
        }

        $liveDimensionIdentifier = $this->getDimensionIdentifier(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveExcerpt = $this->excerptDimensionRepository->findOrCreateDimension($resourceKey, $resourceId, $liveDimensionIdentifier);

        $liveExcerpt->copyAttributesFrom($draftExcerpt);
        $this->copyTags($draftExcerpt, $liveExcerpt);

        return $liveExcerpt;
    }

    protected function getDimensionIdentifier(string $stage, string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = $stage;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }

    private function copyTags(ExcerptDimensionInterface $draftExcerpt, ExcerptDimensionInterface $liveExcerpt): void
    {
        foreach ($liveExcerpt->getTags() as $oldLiveTag) {
            $liveExcerpt->removeTag($oldLiveTag);
            $this->tagReferenceRepository->remove($oldLiveTag);
        }

        foreach ($draftExcerpt->getTags() as $draftTag) {
            $newLiveTag = $this->tagReferenceRepository->create($liveExcerpt, $draftTag->getTag(), $draftTag->getOrder());
            $liveExcerpt->addTag($newLiveTag);
        }
    }
}
