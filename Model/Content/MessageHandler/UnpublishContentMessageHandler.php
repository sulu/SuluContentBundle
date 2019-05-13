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

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\UnpublishContentMessage;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class UnpublishContentMessageHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(UnpublishContentMessage $message): void
    {
        // FIXME this should be removed when no other locale is published
        // $this->unpublishContentDimension($message->getResourceKey(), $message->getResourceId());
        $this->unpublishContentDimension($message->getResourceKey(), $message->getResourceId(), $message->getLocale());
    }

    protected function unpublishContentDimension(string $resourceKey, string $resourceId, ?string $locale = null): void
    {
        $dimensionIdentifier = $this->getDimensionIdentifier(
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
            $locale
        );
        $content = $this->contentDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
        if (!$content) {
            return;
        }

        $this->contentDimensionRepository->removeDimension($content);
    }

    protected function getDimensionIdentifier(string $stage, ?string $locale = null): DimensionIdentifierInterface
    {
        $attributes = [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => $stage];
        if ($locale) {
            $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;
        }

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
