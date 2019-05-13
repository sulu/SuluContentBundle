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
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\UnpublishExcerptMessage;

class UnpublishExcerptMessageHandler
{
    /**
     * @var ExcerptDimensionRepositoryInterface
     */
    private $excerptDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(UnpublishExcerptMessage $message): void
    {
        // FIXME this should be removed when no other locale is published
        // $this->unpublishExcerptDimensions($message->getResourceKey(), $message->getResourceId());
        $this->unpublishExcerptDimensions($message->getResourceKey(), $message->getResourceId(), $message->getLocale());
    }

    protected function unpublishExcerptDimensions(string $resourceKey, string $resourceId, ?string $locale = null): void
    {
        $dimensionIdentifier = $this->getDimensionIdentifier(
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
            $locale
        );
        $content = $this->excerptDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
        if (!$content) {
            return;
        }

        $this->excerptDimensionRepository->removeDimension($content);
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
