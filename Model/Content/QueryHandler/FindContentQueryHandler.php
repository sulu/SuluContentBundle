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

use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Query\FindContentQuery;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;

class FindContentQueryHandler
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentRepositoryInterface $contentRepository,
        DimensionRepositoryInterface $dimensionRepository,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentRepository = $contentRepository;
        $this->dimensionRepository = $dimensionRepository;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(FindContentQuery $query): void
    {
        $dimensions = [
            $this->dimensionRepository->findOrCreateByAttributes($this->createAttributes()),
            $this->dimensionRepository->findOrCreateByAttributes($this->createAttributes($query->getLocale())),
        ];

        $content = $this->contentViewFactory->create(
            $this->contentRepository->findByDimensions(
                $query->getResourceKey(),
                $query->getResourceId(),
                $dimensions
            ),
            $query->getLocale()
        );

        if (!$content) {
            throw new ContentNotFoundException($query->getResourceKey(), $query->getResourceId());
        }

        $query->setContent($content);
    }

    /**
     * @return string[]
     */
    private function createAttributes(?string $locale = null): array
    {
        $attributes = [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT];
        if (!$locale) {
            return $attributes;
        }

        $attributes[DimensionInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
