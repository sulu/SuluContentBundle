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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Component\Util\SortUtils;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @implements \IteratorAggregate<DimensionContentInterface>
 */
class DimensionContentCollection implements \IteratorAggregate, DimensionContentCollectionInterface
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
     * @var mixed[]
     */
    private $defaultDimensionAttributes;

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
        string $dimensionContentClass,
        iterable $mergers,
        PropertyAccessor $propertyAccessor
    ) {
        $this->contentRichEntity = $contentRichEntity;
        $this->dimensionContentClass = $dimensionContentClass;
        $this->defaultDimensionAttributes = $dimensionContentClass::getDefaultDimensionAttributes();
        $this->dimensionAttributes = $dimensionContentClass::getEffectiveDimensionAttributes($dimensionAttributes);
        $this->mergers = $mergers;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getDimensionContentClass(): string
    {
        return $this->dimensionContentClass;
    }

    public function getDimensionContent(array $dimensionAttributes): ?DimensionContentInterface
    {
        $dimensionAttributes = \array_merge($this->defaultDimensionAttributes, $dimensionAttributes);

        $criteria = Criteria::create();
        foreach ($dimensionAttributes as $key => $value) {
            if (null === $value) {
                $expr = $criteria->expr()->isNull($key);
            } else {
                $expr = $criteria->expr()->eq($key, $value);
            }

            $criteria->andWhere($expr);
        }

        return $this->contentRichEntity->getDimensionContents()->matching($criteria)->first() ?: null;
    }

    public function createDimensionContent(array $dimensionAttributes): DimensionContentInterface
    {
        $dimensionContent = $this->contentRichEntity->createDimensionContent();

        foreach ($dimensionAttributes as $attributeName => $attributeValue) {
            $this->propertyAccessor->setValue($dimensionContent, $attributeName, $attributeValue);
        }

        $this->contentRichEntity->addDimensionContent($dimensionContent);

        return $dimensionContent;
    }

    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }

    public function getIterator()
    {
        return new ArrayCollection(SortUtils::multisort(
            $this->contentRichEntity->getDimensionContents()->toArray(),
            \array_keys($this->dimensionAttributes), 'asc'
        ));
    }

    public function count(): int
    {
        return $this->contentRichEntity->getDimensionContents()->count();
    }

    public function getMergedDimensionContent(): DimensionContentInterface
    {
        $unlocalizedDimensionAttributes = $this->dimensionAttributes;
        $unlocalizedDimensionAttributes['locale'] = null;

        $dimensionContents = \array_filter([
            $this->getDimensionContent($unlocalizedDimensionAttributes),
            $this->getDimensionContent($this->dimensionAttributes),
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
