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

use Sulu\Bundle\ContentBundle\Content\Application\Message\SaveContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;

class SaveContentMessageHandler
{
    /**
     * @var DimensionCollectionFactoryInterface
     */
    private $dimensionCollectionFactory;

    /**
     * @var ContentDimensionCollectionFactoryInterface
     */
    private $contentDimensionCollectionFactory;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var ApiViewResolverInterface
     */
    private $viewResolver;

    public function __construct(
        DimensionCollectionFactoryInterface $dimensionCollectionFactory,
        ContentDimensionCollectionFactoryInterface $contentDimensionCollectionFactory,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ) {
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
        $this->contentDimensionCollectionFactory = $contentDimensionCollectionFactory;
        $this->viewFactory = $viewFactory;
        $this->viewResolver = $viewResolver;
    }

    public function __invoke(SaveContentMessage $message): array
    {
        $dimensionCollection = $this->dimensionCollectionFactory->create($message->getDimensionAttributes());
        $contentDimensionCollection = $this->contentDimensionCollectionFactory->create(
            $message->getContent(), $dimensionCollection, $message->getData()
        );
        $contentView = $this->viewFactory->create($contentDimensionCollection);

        return $this->viewResolver->resolve($contentView);
    }
}
