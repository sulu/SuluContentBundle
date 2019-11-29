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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentPersister;

use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

class ContentPersister implements ContentPersisterInterface
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

    public function persist(ContentInterface $content, array $data, array $dimensionAttributes): array
    {
        $dimensionCollection = $this->dimensionCollectionFactory->create($dimensionAttributes);
        $contentDimensionCollection = $this->contentDimensionCollectionFactory->create(
            $content,
            $dimensionCollection,
            $data
        );

        $contentView = $this->viewFactory->create($contentDimensionCollection);

        return $this->viewResolver->resolve($contentView);
    }
}
