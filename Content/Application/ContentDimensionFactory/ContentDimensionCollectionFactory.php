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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\MapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionLoader\ContentDimensionLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentDimensionCollectionFactory implements ContentDimensionCollectionFactoryInterface
{
    /**
     * @var ContentDimensionLoaderInterface
     */
    private $contentDimensionLoader;

    /**
     * @var iterable<MapperInterface>
     */
    private $mappers;

    /**
     * @param iterable<MapperInterface> $mappers
     */
    public function __construct(ContentDimensionLoaderInterface $contentDimensionLoader, iterable $mappers)
    {
        $this->contentDimensionLoader = $contentDimensionLoader;
        $this->mappers = $mappers;
    }

    public function create(
        ContentInterface $content,
        DimensionCollectionInterface $dimensionCollection,
        array $data
    ): ContentDimensionCollectionInterface {
        $contentDimensionCollection = $this->contentDimensionLoader->load($content, $dimensionCollection);

        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();

        $contentDimensions = new ArrayCollection(iterator_to_array($contentDimensionCollection));

        if (!$unlocalizedDimension) {
            throw new \RuntimeException('The "$dimensionCollection" should contain atleast a unlocalizedDimension.');
        }

        $unlocalizedContentDimension = $this->getOrCreateContentDimension(
            $content,
            $contentDimensions,
            $unlocalizedDimension
        );

        $localizedContentDimension = null;
        if ($localizedDimension) {
            $localizedContentDimension = $this->getOrCreateContentDimension(
                $content,
                $contentDimensions,
                $localizedDimension
            );
        }

        // Sort correctly ContentDimensions by given dimensionIds to merge them later correctly
        $orderedContentDimensions = [];
        foreach ($dimensionCollection as $key => $dimension) {
            $contentDimension = $contentDimensions->filter(function (ContentDimensionInterface $contentDimension) use ($dimension) {
                return $contentDimension->getDimension()->getId() === $dimension->getId();
            })->first();

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
        DimensionInterface $dimension
    ): ContentDimensionInterface {
        $contentDimension = $contentDimensions->filter(function (ContentDimensionInterface $contentDimension) use ($dimension) {
            return $contentDimension->getDimension()->getId() === $dimension->getId();
        })->first();

        if (!$contentDimension) {
            $contentDimension = $content->createDimension($dimension);
            $content->addDimension($contentDimension);
            $contentDimensions->add($contentDimension);
        }

        return $contentDimension;
    }
}
