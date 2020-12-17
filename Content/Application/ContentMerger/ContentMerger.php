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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentMerger;

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class ContentMerger implements ContentMergerInterface
{
    /**
     * @var iterable<MergerInterface>
     */
    private $mergers;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param iterable<MergerInterface> $mergers
     */
    public function __construct(
        iterable $mergers,
        PropertyAccessor $propertyAccessor
    ) {
        $this->mergers = $mergers;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function merge(DimensionContentCollectionInterface $dimensionContentCollection): DimensionContentInterface
    {
        $unlocalizedDimensionContent = $dimensionContentCollection->getUnlocalizedDimensionContent();

        if (!$unlocalizedDimensionContent) {
            throw new \RuntimeException('Expected at least one dimensionContent given.');
        }

        $contentRichEntity = $unlocalizedDimensionContent->getResource();

        $mergedDimensionContent = $contentRichEntity->createDimensionContent();
        $mergedDimensionContent->markAsMerged();

        foreach ($dimensionContentCollection as $dimensionContent) {
            foreach ($this->mergers as $merger) {
                $merger->merge($mergedDimensionContent, $dimensionContent);
            }

            foreach ($dimensionContent::getDefaultAttributes() as $key => $value) {
                $this->propertyAccessor->setValue(
                    $mergedDimensionContent,
                    $key,
                    $this->propertyAccessor->getValue($dimensionContent, $key)
                );
            }
        }

        return $mergedDimensionContent;
    }
}
