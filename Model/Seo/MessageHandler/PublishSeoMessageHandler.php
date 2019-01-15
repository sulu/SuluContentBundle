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
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\PublishSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class PublishSeoMessageHandler
{
    /**
     * @var SeoDimensionRepositoryInterface
     */
    private $seoDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var SeoViewFactoryInterface
     */
    private $seoViewFactory;

    public function __construct(
        SeoDimensionRepositoryInterface $seoDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        SeoViewFactoryInterface $seoViewFactory
    ) {
        $this->seoDimensionRepository = $seoDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->seoViewFactory = $seoViewFactory;
    }

    public function __invoke(PublishSeoMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();
        $mandatory = $message->isMandatory();

        $publishedSeoDimensions = array_filter([
            $this->publishSeoDimensions($resourceKey, $resourceId, $mandatory, $message->getLocale()),
        ]);

        if (!$publishedSeoDimensions) {
            return;
        }

        $seoView = $this->seoViewFactory->create($publishedSeoDimensions, $message->getLocale());
        if (!$seoView) {
            throw new SeoNotFoundException($resourceKey, $resourceId);
        }

        $message->setSeo($seoView);
    }

    protected function publishSeoDimensions(
        string $resourceKey,
        string $resourceId,
        bool $mandatory,
        string $locale
    ): ?SeoDimensionInterface {
        $draftDimensionIdentifier = $this->getDimensionIdentifier(DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT, $locale);
        $draftSeo = $this->seoDimensionRepository->findByResource($resourceKey, $resourceId, $draftDimensionIdentifier);

        if (!$draftSeo) {
            if (!$mandatory) {
                return null;
            }

            throw new SeoNotFoundException($resourceKey, $resourceId);
        }

        $liveDimensionIdentifier = $this->getDimensionIdentifier(DimensionIdentifierInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveSeo = $this->seoDimensionRepository->findOrCreate($resourceKey, $resourceId, $liveDimensionIdentifier);

        $liveSeo->copyAttributesFrom($draftSeo);

        return $liveSeo;
    }

    protected function getDimensionIdentifier(string $stage, string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = $stage;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
