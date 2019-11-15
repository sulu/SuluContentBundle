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

namespace Sulu\Bundle\ContentBundle\Content\Application\MessageHandler;

use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionLoader\ContentDimensionLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentViewMessage;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class LoadContentViewMessageHandler
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentDimensionLoaderInterface
     */
    private $contentDimensionLoader;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionLoaderInterface $contentDimensionLoader,
        ViewFactoryInterface $viewFactory
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionLoader = $contentDimensionLoader;
        $this->viewFactory = $viewFactory;
    }

    public function __invoke(LoadContentViewMessage $message): ContentViewInterface
    {
        $content = $message->getContent();
        $dimensionCollection = $this->dimensionRepository->findByAttributes($message->getDimensionAttributes());
        $contentDimensionCollection = $this->contentDimensionLoader->load($content, $dimensionCollection);

        return $this->viewFactory->create($contentDimensionCollection);
    }
}
