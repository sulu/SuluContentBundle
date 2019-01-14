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

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
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
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var SeoViewFactoryInterface
     */
    private $seoViewFactory;

    public function __construct(
        SeoDimensionRepositoryInterface $seoDimensionRepository,
        DimensionRepositoryInterface $dimensionRepository,
        SeoViewFactoryInterface $seoViewFactory
    ) {
        $this->seoDimensionRepository = $seoDimensionRepository;
        $this->dimensionRepository = $dimensionRepository;
        $this->seoViewFactory = $seoViewFactory;
    }

    public function __invoke(PublishSeoMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();
        $mandatory = $message->isMandatory();

        $liveSeos = array_filter([
            $this->publishSeo($resourceKey, $resourceId, $mandatory, $message->getLocale()),
        ]);

        if (!$liveSeos) {
            return;
        }

        $seoView = $this->seoViewFactory->create($liveSeos, $message->getLocale());
        if (!$seoView) {
            throw new SeoNotFoundException($resourceKey, $resourceId);
        }

        $message->setSeo($seoView);
    }

    protected function publishSeo(
        string $resourceKey,
        string $resourceId,
        bool $mandatory,
        string $locale
    ): ?SeoDimensionInterface {
        $draftAttributes = $this->createAttributes(DimensionInterface::ATTRIBUTE_VALUE_DRAFT, $locale);
        $draftDimension = $this->dimensionRepository->findOrCreateByAttributes($draftAttributes);
        $draftSeo = $this->seoDimensionRepository->findByResource($resourceKey, $resourceId, $draftDimension);

        if (!$draftSeo) {
            if (!$mandatory) {
                return null;
            }

            throw new SeoNotFoundException($resourceKey, $resourceId);
        }

        $liveAttributes = $this->createAttributes(DimensionInterface::ATTRIBUTE_VALUE_LIVE, $locale);
        $liveDimension = $this->dimensionRepository->findOrCreateByAttributes($liveAttributes);
        $liveSeo = $this->seoDimensionRepository->findOrCreate($resourceKey, $resourceId, $liveDimension);

        $liveSeo->copyAttributesFrom($draftSeo);

        return $liveSeo;
    }

    protected function createAttributes(string $stage, string $locale): array
    {
        $attributes = [];
        $attributes[DimensionInterface::ATTRIBUTE_KEY_STAGE] = $stage;
        $attributes[DimensionInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
