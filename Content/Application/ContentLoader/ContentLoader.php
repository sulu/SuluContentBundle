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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentLoader;

use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentLoader implements ContentLoaderInterface
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->viewFactory = $viewFactory;
    }

    public function load(ContentInterface $content, array $dimensionAttributes): ContentViewInterface
    {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);

        if (0 === \count($dimensionCollection)) {
            throw new ContentNotFoundException($content, $dimensionAttributes);
        }

        $contentDimensionCollection = $this->contentDimensionRepository->load($content, $dimensionCollection);

        if (0 === \count($contentDimensionCollection)) {
            throw new ContentNotFoundException($content, $dimensionAttributes);
        }

        return $this->viewFactory->create($contentDimensionCollection);
    }
}
