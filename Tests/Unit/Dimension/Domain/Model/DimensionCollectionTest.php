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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Dimension\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;

class DimensionCollectionTest extends TestCase
{
    /**
     * @param mixed[] $attributes
     * @param DimensionInterface[] $dimensions
     */
    protected function createDimensionCollection(
        array $attributes = [],
        array $dimensions = []
    ): DimensionCollectionInterface {
        return new DimensionCollection($attributes, $dimensions);
    }

    /**
     * @param mixed[] $attributes
     */
    protected function createDimension(?string $id, array $attributes = []): DimensionInterface
    {
        return new Dimension($id, $attributes);
    }

    public function testGetAttributesEmpty(): void
    {
        $dimensionCollection = $this->createDimensionCollection();
        $this->assertSame([], $dimensionCollection->getAttributes());
    }

    public function testGetAttributesWithValues(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ]);

        $this->assertSame([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], $dimensionCollection->getAttributes());
    }

    public function testGetDimensionIds(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $this->createDimension('123-456'),
            $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertSame([
            '123-456',
            '456-789',
        ], $dimensionCollection->getDimensionIds());
    }

    public function testGetLocalizedDimensionEmpty(): void
    {
        $dimensionCollection = $this->createDimensionCollection();
        $this->assertNull($dimensionCollection->getLocalizedDimension());
    }

    public function testGetUnlocalizedDimensionEmpty(): void
    {
        $dimensionCollection = $this->createDimensionCollection();
        $this->assertNull($dimensionCollection->getUnlocalizedDimension());
    }

    public function testGetUnLocalizedDimension(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $unlocalizedDimension = $this->createDimension('123-456'),
            $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertSame($unlocalizedDimension, $dimensionCollection->getUnlocalizedDimension());
    }

    public function testGetLocalizedDimension(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $this->createDimension('123-456'),
            $localizedDimdension = $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertSame($localizedDimdension, $dimensionCollection->getLocalizedDimension());
    }

    public function testGetUnLocalizedDimensionAttributes(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $unlocalizedDimension = $this->createDimension('123-456'),
            $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertSame([
            'locale' => null,
            'workflowStage' => 'draft',
        ], $dimensionCollection->getUnlocalizedAttributes());
    }

    public function testGetLocalizedDimensionAttributes(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $this->createDimension('123-456'),
            $localizedDimdension = $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertSame([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], $dimensionCollection->getLocalizedAttributes());
    }

    public function testGetCount(): void
    {
        $dimensionCollection = $this->createDimensionCollection([
            'locale' => 'de',
            'workflowStage' => 'draft',
        ], [
            $this->createDimension('123-456'),
            $this->createDimension('456-789', ['locale' => 'de']),
        ]);

        $this->assertCount(2, $dimensionCollection);
    }
}
