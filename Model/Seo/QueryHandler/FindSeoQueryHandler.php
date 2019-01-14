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

namespace Sulu\Bundle\ContentBundle\Model\Seo\QueryHandler;

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Factory\SeoViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Query\FindSeoQuery;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class FindSeoQueryHandler
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

    public function __invoke(FindSeoQuery $query): void
    {
        $dimensionIdentifiers = [
            $this->dimensionIdentifierRepository->findOrCreateByAttributes($this->createAttributes($query->getLocale())),
        ];

        $seoView = $this->seoViewFactory->create(
            $this->seoDimensionRepository->findByDimensionIdentifiers(
                $query->getResourceKey(),
                $query->getResourceId(),
                $dimensionIdentifiers
            ),
            $query->getLocale()
        );

        if (!$seoView) {
            throw new SeoNotFoundException($query->getResourceKey(), $query->getResourceId());
        }

        $query->setSeo($seoView);
    }

    /**
     * @return string[]
     */
    private function createAttributes(string $locale): array
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
