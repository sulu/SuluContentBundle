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

use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
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

    public function __invoke(FindSeoQuery $query): void
    {
        $dimensions = [
            $this->dimensionRepository->findOrCreateByAttributes($this->createAttributes($query->getLocale())),
        ];

        $seo = $this->seoViewFactory->create(
            $this->seoDimensionRepository->findByDimensions(
                $query->getResourceKey(),
                $query->getResourceId(),
                $dimensions
            ),
            $query->getLocale()
        );

        if (!$seo) {
            throw new SeoNotFoundException($query->getResourceKey(), $query->getResourceId());
        }

        $query->setSeo($seo);
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
