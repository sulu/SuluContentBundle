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

use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\PublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;

class PublishContentMessageHandler
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentRepositoryInterface $contentRepository,
        DimensionRepositoryInterface $dimensionRepository,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentRepository = $contentRepository;
        $this->dimensionRepository = $dimensionRepository;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(PublishContentMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();
        $mandatory = $message->isMandatory();

        $contents = array_filter([
            $this->publishContent($resourceKey, $resourceId, $mandatory),
            $this->publishContent($resourceKey, $resourceId, $mandatory, $message->getLocale()),
        ]);

        if (!$contents) {
            return;
        }

        $contentView = $this->contentViewFactory->create($contents, $message->getLocale());
        if (!$contentView) {
            throw new ContentNotFoundException($resourceKey, $resourceId);
        }

        $message->setContent($contentView);
    }

    protected function publishContent(
        string $resourceKey,
        string $resourceId,
        bool $mandatory,
        ?string $locale = null
    ): ?ContentInterface {
        $draftAttributes = $this->createAttributes(DimensionInterface::ATTRIBUTE_VALUE_DRAFT, $locale);
        $draftDimension = $this->dimensionRepository->findOrCreateByAttributes($draftAttributes);
        $draftContent = $this->contentRepository->findByResource($resourceKey, $resourceId, $draftDimension);

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

        $liveAttributes = $this->createAttributes(DimensionInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveDimension = $this->dimensionRepository->findOrCreateByAttributes($liveAttributes);
        $liveContent = $this->contentRepository->findOrCreate($resourceKey, $resourceId, $liveDimension);

        $liveContent->copyAttributesFrom($draftContent);

        return $liveContent;
    }

    protected function createAttributes(string $stage, ?string $locale = null): array
    {
        $attributes = [DimensionInterface::ATTRIBUTE_KEY_STAGE => $stage];
        if (!$locale) {
            return $attributes;
        }

        $attributes[DimensionInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
