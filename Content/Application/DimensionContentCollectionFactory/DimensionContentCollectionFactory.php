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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentCollectionFactory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class DimensionContentCollectionFactory implements DimensionContentCollectionFactoryInterface
{
    /**
     * @var DimensionContentRepositoryInterface
     */
    private $dimensionContentRepository;

    /**
     * @var ContentDataMapperInterface
     */
    private $contentDataMapper;

    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    public function __construct(
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ContentDataMapperInterface $contentDataMapper,
        PropertyAccessor $propertyAccessor
    ) {
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->contentDataMapper = $contentDataMapper;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function create(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        array $data
    ): DimensionContentCollectionInterface {
        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $dimensionAttributes);
        $dimensionAttributes = $dimensionContentCollection->getDimensionAttributes();

        $orderedContentDimensions = \iterator_to_array($dimensionContentCollection);
        $dimensionContents = new ArrayCollection($orderedContentDimensions);

        $unlocalizedAttributes = $dimensionAttributes;
        $unlocalizedAttributes['locale'] = null;

        // get or create unlocalized dimension content
        $unlocalizedDimensionContent = $dimensionContentCollection->getDimensionContent($unlocalizedAttributes);

        if (!$unlocalizedDimensionContent) {
            $unlocalizedDimensionContent = $this->createContentDimension(
                $contentRichEntity,
                $dimensionContents,
                $unlocalizedAttributes
            );
            $orderedContentDimensions[] = $unlocalizedDimensionContent;
        }

        $localizedDimensionContent = null;
        if (isset($dimensionAttributes['locale'])) {
            // get or create localized dimension content
            $localizedDimensionContent = $dimensionContentCollection->getDimensionContent($dimensionAttributes);

            if (!$localizedDimensionContent) {
                $localizedDimensionContent = $this->createContentDimension(
                    $contentRichEntity,
                    $dimensionContents,
                    $dimensionAttributes
                );
                $orderedContentDimensions[] = $localizedDimensionContent;
            }
        }

        $dimensionContentCollection = new DimensionContentCollection(
            $orderedContentDimensions,
            $dimensionAttributes,
            $dimensionContentCollection->getDimensionContentClass()
        );

        $this->contentDataMapper->map($data, $dimensionContentCollection);

        return $dimensionContentCollection;
    }

    /**
     * @param Collection<int, DimensionContentInterface> $dimensionContents
     * @param mixed[] $attributes
     */
    private function createContentDimension(
        ContentRichEntityInterface $contentRichEntity,
        Collection $dimensionContents,
        array $attributes
    ): DimensionContentInterface {
        $dimensionContent = $contentRichEntity->createDimensionContent();

        foreach ($attributes as $attributeName => $attributeValue) {
            $this->propertyAccessor->setValue($dimensionContent, $attributeName, $attributeValue);
        }

        $contentRichEntity->addDimensionContent($dimensionContent);
        $dimensionContents->add($dimensionContent);

        return $dimensionContent;
    }
}
