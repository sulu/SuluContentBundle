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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\QueryHandler;

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Factory\ExcerptViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Query\FindExcerptQuery;

class FindExcerptQueryHandler
{
    /**
     * @var ExcerptDimensionRepositoryInterface
     */
    private $excerptDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var ExcerptViewFactoryInterface
     */
    private $excerptViewFactory;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        ExcerptViewFactoryInterface $excerptViewFactory
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->excerptViewFactory = $excerptViewFactory;
    }

    public function __invoke(FindExcerptQuery $query): void
    {
        $excerptView = $this->excerptViewFactory->create(
            $this->excerptDimensionRepository->findByDimensionIdentifiers(
                $query->getResourceKey(),
                $query->getResourceId(),
                [$this->getDraftDimensionIdentifier($query->getLocale())]
            ),
            $query->getLocale()
        );

        if (!$excerptView) {
            throw new ExcerptNotFoundException($query->getResourceKey(), $query->getResourceId());
        }

        $query->setExcerpt($excerptView);
    }

    private function getDraftDimensionIdentifier(string $locale): DimensionIdentifierInterface
    {
        $attributes = [];
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE] = DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT;
        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
