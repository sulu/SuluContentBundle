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

namespace Sulu\Bundle\ContentBundle\Model\Content\QueryHandler;

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Query\FindContentQuery;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class FindContentQueryHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentRepository = $contentDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(FindContentQuery $query): void
    {
        $dimensionIdentifiers = [
            $this->dimensionIdentifierRepository->findOrCreateByAttributes($this->createAttributes()),
            $this->dimensionIdentifierRepository->findOrCreateByAttributes($this->createAttributes($query->getLocale())),
        ];

        $contentView = $this->contentViewFactory->create(
            $this->contentRepository->findByDimensionIdentifiers(
                $query->getResourceKey(),
                $query->getResourceId(),
                $dimensionIdentifiers
            ),
            $query->getLocale()
        );

        if (!$contentView) {
            throw new ContentNotFoundException($query->getResourceKey(), $query->getResourceId());
        }

        $query->setContent($contentView);
    }

    /**
     * @return string[]
     */
    private function createAttributes(?string $locale = null): array
    {
        $attributes = [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT];
        if (!$locale) {
            return $attributes;
        }

        $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
