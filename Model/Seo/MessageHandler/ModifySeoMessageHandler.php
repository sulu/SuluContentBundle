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
use Sulu\Bundle\ContentBundle\Model\Seo\Message\ModifySeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class ModifySeoMessageHandler
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

    public function __invoke(ModifySeoMessage $message): void
    {
        $localizedDraftSeo = $this->findOrCreateSeo(
            $message->getResourceKey(),
            $message->getResourceId(),
            $message->getLocale()
        );
        $this->setData($message, $localizedDraftSeo);

        $seoView = $this->seoViewFactory->create([$localizedDraftSeo], $message->getLocale());
        if (!$seoView) {
            throw new SeoNotFoundException($message->getResourceKey(), $message->getResourceId());
        }

        $message->setSeo($seoView);
    }

    private function setData(
        ModifySeoMessage $message,
        SeoDimensionInterface $localizedDraftSeo
    ): void {
        $localizedDraftSeo->setTitle($message->getTitle());
        $localizedDraftSeo->setDescription($message->getDescription());
        $localizedDraftSeo->setKeywords($message->getKeywords());
        $localizedDraftSeo->setCanonicalUrl($message->getCanonicalUrl());
        $localizedDraftSeo->setNoIndex($message->getNoIndex());
        $localizedDraftSeo->setNoFollow($message->getNoFollow());
        $localizedDraftSeo->setHideInSitemap($message->getHideInSitemap());
    }

    private function findOrCreateSeo(
        string $resourceKey,
        string $resourceId,
        string $locale
    ): SeoDimensionInterface {
        $dimension = $this->dimensionRepository->findOrCreateByAttributes($this->createAttributes($locale));

        return $this->seoDimensionRepository->findOrCreate($resourceKey, $resourceId, $dimension);
    }

    /**
     * @return string[]
     */
    private function createAttributes(string $locale): array
    {
        $attributes = [];
        $attributes[DimensionInterface::ATTRIBUTE_KEY_STAGE] = DimensionInterface::ATTRIBUTE_VALUE_DRAFT;
        $attributes[DimensionInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
