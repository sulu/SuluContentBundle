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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @implements \IteratorAggregate<DimensionContentInterface>
 */
class DimensionContentCollection implements DimensionContentCollectionInterface
{
    /**
     * @var ContentRichEntityInterface
     */
    private $contentRichEntity;

    /**
     * @var mixed[]
     */
    private $dimensionAttributes;

    /**
     * @var class-string<DimensionContentInterface>
     */
    private $dimensionContentClass;

    /**
     * @var iterable<MergerInterface>
     */
    private $mergers;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * DimensionContentCollection constructor.
     *
     * @param ContentRichEntityInterface $contentRichEntity
     * @param mixed[] $dimensionAttributes
     * @param class-string<DimensionContentInterface> $dimensionContentClass
     * @param iterable<MergerInterface>
     */
    public function __construct(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        iterable $mergers,
        PropertyAccessor $propertyAccessor
    ) {
        $this->contentRichEntity = $contentRichEntity;
        $this->dimensionContentClass = $contentRichEntity::getDimensionContentClass();
        $this->dimensionAttributes = $this->dimensionContentClass::getEffectiveDimensionAttributes($dimensionAttributes);
        $this->mergers = $mergers;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getResource(): ContentRichEntityInterface
    {
        return $this->contentRichEntity;
    }

    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }

    public function getMergedDimensionContent(): DimensionContentInterface
    {
        $unlocalizedDimensionAttributes = $this->dimensionAttributes;
        $unlocalizedDimensionAttributes['locale'] = null;

        $dimensionContents = \array_filter([
            $this->contentRichEntity->findDimensionContent($unlocalizedDimensionAttributes),
            $this->contentRichEntity->findDimensionContent($this->dimensionAttributes),
        ]);

        $mergedDimensionContent = null;

        foreach ($dimensionContents as $dimensionContent) {
            if (!$mergedDimensionContent) {
                $contentRichEntity = $dimensionContent->getResource();
                $mergedDimensionContent = $contentRichEntity->createDimensionContent();
                $mergedDimensionContent->markAsMerged();
            }

            foreach ($this->mergers as $merger) {
                $merger->merge($mergedDimensionContent, $dimensionContent);
            }

            foreach ($this->dimensionAttributes as $key => $value) {
                $this->propertyAccessor->setValue(
                    $mergedDimensionContent,
                    $key,
                    $this->propertyAccessor->getValue($dimensionContent, $key)
                );
            }
        }

        if (!$mergedDimensionContent) {
            throw new \RuntimeException('Expected at least one dimensionContent given.');
        }

        return $mergedDimensionContent;
    }
}
