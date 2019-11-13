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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;

class LoadContentMessageHandler
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

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
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->viewFactory = $viewFactory;
        $this->viewResolver = $viewResolver;
    }

    public function __invoke(LoadContentMessage $message): array
    {
        $content = $message->getContent();
        $dimensionCollection = $this->dimensionRepository->findByAttributes($message->getDimensionAttributes());

        // TODO this part is very similiar to ContentDimensionCollectionFactory and should be refractored
        $dimensionIds = $dimensionCollection->getDimensionIds();

        /** @var Collection $contentDimensions */
        $contentDimensions = $content->getDimensions();

        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->in('dimensionId', $dimensionIds));
        $contentDimensions = $contentDimensions->matching($criteria);

        $orderedContentDimensions = [];

        foreach ($dimensionIds as $key => $dimensionId) {
            $criteria = Criteria::create();
            $criteria->andWhere($criteria->expr()->eq('dimensionId', $dimensionId));
            $contentDimension = $contentDimensions->matching($criteria)->first();

            if ($contentDimension) {
                $orderedContentDimensions[$key] = $contentDimension;
            }
        }

        // TODO end of the similiar part to the ContentDimensionCollectionFactory

        $contentView = $this->viewFactory->create(new ContentDimensionCollection($orderedContentDimensions));

        return $this->viewResolver->resolve($contentView);
    }
}
