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

namespace Sulu\Bundle\ContentBundle\Model\Content\MessageHandler;

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class PublishContentMessageHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(PublishContentMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();
        $mandatory = $message->isMandatory();

        $publishedContentDimensions = array_filter([
            $this->publishContentDimension($resourceKey, $resourceId, $mandatory),
            $this->publishContentDimension($resourceKey, $resourceId, $mandatory, $message->getLocale()),
        ]);

        if (!$publishedContentDimensions) {
            return;
        }

        $contentView = $this->contentViewFactory->create($publishedContentDimensions, $message->getLocale());
        if (!$contentView) {
            throw new ContentNotFoundException($resourceKey, $resourceId);
        }

        $message->setContent($contentView);
    }

    protected function publishContentDimension(
        string $resourceKey,
        string $resourceId,
        bool $mandatory,
        ?string $locale = null
    ): ?ContentDimensionInterface {
        $draftAttributes = $this->createAttributes(DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT, $locale);
        $draftDimensionIdentifier = $this->dimensionIdentifierRepository->findOrCreateByAttributes($draftAttributes);
        $draftContent = $this->contentDimensionRepository->findByResource($resourceKey, $resourceId, $draftDimensionIdentifier);

        if (!$draftContent) {
            if (!$mandatory) {
                return null;
            }

            throw new ContentNotFoundException($resourceKey, $resourceId);
        }

        $type = $draftContent->getType();
        if (!$type) {
            throw new \InvalidArgumentException('Content type cannot be null');
        }

        $liveAttributes = $this->createAttributes(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveDimensionIdentifier = $this->dimensionIdentifierRepository->findOrCreateByAttributes($liveAttributes);
        $liveContent = $this->contentDimensionRepository->findOrCreate($resourceKey, $resourceId, $liveDimensionIdentifier);

        $liveContent->copyAttributesFrom($draftContent);

        return $liveContent;
    }

    protected function createAttributes(string $stage, ?string $locale = null): array
    {
        $attributes = [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => $stage];
        if (!$locale) {
            return $attributes;
        }

        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
