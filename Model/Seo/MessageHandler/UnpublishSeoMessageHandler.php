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

namespace Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler;

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\UnpublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class UnpublishSeoMessageHandler
{
    /**
     * @var SeoDimensionRepositoryInterface
     */
    private $seoDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        SeoDimensionRepositoryInterface $seoDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->seoDimensionRepository = $seoDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(UnpublishSeoMessage $message): void
    {
        $this->unpublishSeoDimensions($message->getResourceKey(), $message->getResourceId(), $message->getLocale());
    }

    protected function unpublishSeoDimensions(string $resourceKey, string $resourceId, string $locale): void
    {
        $dimensionIdentifier = $this->getDimensionIdentifier(
            DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE,
            $locale
        );
        $seo = $this->seoDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
        if (!$seo) {
            return;
        }

        $this->seoDimensionRepository->removeDimension($seo);
    }

    protected function getDimensionIdentifier(string $stage, string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = $stage;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
