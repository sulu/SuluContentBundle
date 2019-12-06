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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentWorkflow
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

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function transition(
        ContentInterface $content,
        array $dimensionAttributes,
        string $workflowPlace
    ): ContentViewInterface {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);
        $contentDimensionCollection = $this->contentDimensionRepository->load($content, $dimensionCollection);

        return $this->viewFactory->create($contentDimensionCollection);
    }
}
