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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\MapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollectionInterface;

class ContentDimensionCollectionFactory implements ContentDimensionCollectionFactoryInterface
{
    /**
     * @var iterable<MapperInterface>
     */
    private $mappers;

    /**
     * @param iterable<MapperInterface> $mappers
     */
    public function __construct(iterable $mappers)
    {
        $this->mappers = $mappers;
    }

    public function create(
        ContentInterface $content,
        DimensionCollectionInterface $dimensionCollection,
        array $data
    ): ContentDimensionCollectionInterface {
        $dimensionIds = $dimensionCollection->getDimensionIds();
        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();

        /** @var Collection $contentDimensions */
        $contentDimensions = $content->getDimensions();

        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->in('dimensionId', $dimensionIds));

        $contentDimensions = $contentDimensions->matching($criteria);

        $localizedContentDimension = null;
        if ($localizedDimension) {
            $localizedContentDimension = $this->getOrCreateContentDimension(
                $content,
                $contentDimensions,
                $localizedDimension->getId()
            );
        }

        if (!$unlocalizedDimension) {
            throw new \RuntimeException();
        }

        $unlocalizedContentDimension = $this->getOrCreateContentDimension(
            $content,
            $contentDimensions,
            $unlocalizedDimension->getId()
        );

        $orderedContentDimensions = [];

        foreach ($dimensionIds as $key => $dimensionId) {
            $criteria = Criteria::create();
            $criteria->andWhere($criteria->expr()->eq('dimensionId', $dimensionId));
            $contentDimension = $contentDimensions->matching($criteria)->first();

            if ($contentDimension) {
                $orderedContentDimensions[$key] = $contentDimension;
            }
        }

        foreach ($this->mappers as $mapper) {
            $mapper->map($data, $unlocalizedContentDimension, $localizedContentDimension);
        }

        return new ContentDimensionCollection($orderedContentDimensions);
    }

    private function getOrCreateContentDimension(
        ContentInterface $content,
        Collection $contentDimensions,
        string $dimensionId
    ): ContentDimensionInterface {
        $criteria = Criteria::create();
        $criteria->andWhere($criteria->expr()->eq('dimensionId', $dimensionId));
        $contentDimension = $contentDimensions->matching($criteria)->first();

        if (!$contentDimension) {
            $contentDimension = $content->createDimension($dimensionId);
            $content->addDimension($contentDimension);
            $contentDimensions->add($contentDimension);
        }

        return $contentDimension;
    }
}
