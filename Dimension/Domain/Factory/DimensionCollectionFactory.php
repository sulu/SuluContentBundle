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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;

class DimensionCollectionFactory implements DimensionCollectionFactoryInterface
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    public function __construct(DimensionRepositoryInterface $dimensionRepository)
    {
        $this->dimensionRepository = $dimensionRepository;
    }

    /**
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    public function create(array $dimensionAttributes): DimensionCollectionInterface
    {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);

        $unlocalizedDimension = $dimensionCollection->getUnlocalizedDimension();
        $localizedDimension = $dimensionCollection->getLocalizedDimension();
        $localizedAttributes = $dimensionCollection->getLocalizedAttributes();

        if ($unlocalizedDimension
            && ($localizedDimension || !$localizedAttributes)
        ) {
            return $dimensionCollection;
        }

        $dimensions = iterator_to_array($dimensionCollection);

        if (!$unlocalizedDimension) {
            $unlocalizedDimension = $this->dimensionRepository->create(
                null,
                $dimensionCollection->getUnlocalizedAttributes()
            );
            $this->dimensionRepository->add($unlocalizedDimension);
            $dimensions[] = $unlocalizedDimension;
        }

        if (!$localizedDimension && $localizedAttributes = $dimensionCollection->getLocalizedAttributes()) {
            $localizedDimension = $this->dimensionRepository->create(null, $localizedAttributes);
            $this->dimensionRepository->add($localizedDimension);
            $dimensions[] = $localizedDimension;
        }

        $criteria = Criteria::create()->orderBy(
            array_fill_keys(array_keys($dimensionCollection->getAttributes()), 'asc')
        );

        return new DimensionCollection(
            $dimensionCollection->getAttributes(),
            (new ArrayCollection($dimensions))->matching($criteria)->toArray()
        );
    }
}
