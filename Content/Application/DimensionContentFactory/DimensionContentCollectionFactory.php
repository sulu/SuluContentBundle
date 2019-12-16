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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper\MapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;

class DimensionContentCollectionFactory implements DimensionContentCollectionFactoryInterface
{
    /**
     * @var DimensionContentRepositoryInterface
     */
    private $dimensionContentRepository;

    /**
     * @var iterable<MapperInterface>
     */
    private $mappers;

    /**
     * @param iterable<MapperInterface> $mappers
     */
    public function __construct(DimensionContentRepositoryInterface $dimensionContentRepository, iterable $mappers)
    {
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->mappers = $mappers;
    }

    public function create(
        ContentRichEntityInterface $contentRichEntity,
        DimensionCollectionInterface $dimensionCollection,
        array $data
    ): DimensionContentCollectionInterface {
        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();

        $dimensionContents = new ArrayCollection(iterator_to_array($dimensionContentCollection));

        if (!$unlocalizedDimension) {
            throw new \RuntimeException('The "$dimensionCollection" should contain atleast a unlocalizedDimension.');
        }

        $unlocalizedDimensionContent = $this->getOrCreateContentDimension(
            $contentRichEntity,
            $dimensionContents,
            $unlocalizedDimension
        );

        $localizedDimensionContent = null;
        if ($localizedDimension) {
            $localizedDimensionContent = $this->getOrCreateContentDimension(
                $contentRichEntity,
                $dimensionContents,
                $localizedDimension
            );
        }

        // Sort correctly ContentDimensions by given dimensionIds to merge them later correctly
        $orderedContentDimensions = [];
        foreach ($dimensionCollection as $key => $dimension) {
            $dimensionContent = $dimensionContents->filter(function (DimensionContentInterface $dimensionContent) use ($dimension) {
                return $dimensionContent->getDimension()->getId() === $dimension->getId();
            })->first();

            if ($dimensionContent) {
                $orderedContentDimensions[$key] = $dimensionContent;
            }
        }

        foreach ($this->mappers as $mapper) {
            $mapper->map($data, $unlocalizedDimensionContent, $localizedDimensionContent);
        }

        return new DimensionContentCollection($orderedContentDimensions, $dimensionCollection);
    }

    private function getOrCreateContentDimension(
        ContentRichEntityInterface $contentRichEntity,
        Collection $dimensionContents,
        DimensionInterface $dimension
    ): DimensionContentInterface {
        $dimensionContent = $dimensionContents->filter(function (DimensionContentInterface $dimensionContent) use ($dimension) {
            return $dimensionContent->getDimension()->getId() === $dimension->getId();
        })->first();

        if (!$dimensionContent) {
            $dimensionContent = $contentRichEntity->createDimensionContent($dimension);
            $contentRichEntity->addDimensionContent($dimensionContent);
            $dimensionContents->add($dimensionContent);
        }

        return $dimensionContent;
    }
}
