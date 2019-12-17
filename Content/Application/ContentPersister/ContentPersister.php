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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

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
        ViewFactoryInterface $viewFactory
    ) {
        $this->dimensionCollectionFactory = $dimensionCollectionFactory;
        $this->contentDimensionCollectionFactory = $contentDimensionCollectionFactory;
        $this->viewFactory = $viewFactory;
    }

    public function persist(ContentRichEntityInterface $contentRichEntity, array $data, array $dimensionAttributes): ContentViewInterface
    {
        $dimensionCollection = $this->dimensionCollectionFactory->create($dimensionAttributes);
        $contentDimensionCollection = $this->contentDimensionCollectionFactory->create(
            $contentRichEntity,
            $dimensionCollection,
            $data
        );

        return $this->viewFactory->create($contentDimensionCollection);
    }
}
