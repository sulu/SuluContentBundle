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

use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class LoadContentMessageHandler
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

    /**
     * @var ApiViewResolverInterface
     */
    private $viewResolver;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->viewFactory = $viewFactory;
        $this->viewResolver = $viewResolver;
    }

    public function __invoke(LoadContentMessage $message): array
    {
        $content = $message->getContent();
        $dimensionCollection = $this->dimensionRepository->findByAttributes($message->getDimensionAttributes());
        $contentDimensionCollection = $this->contentDimensionRepository->load($content, $dimensionCollection);
        $contentView = $this->viewFactory->create($contentDimensionCollection);

        return $this->viewResolver->resolve($contentView);
    }
}
