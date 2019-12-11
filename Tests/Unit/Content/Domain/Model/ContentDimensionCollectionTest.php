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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;

class ContentDimensionCollectionTest extends TestCase
{
    /**
     * @param ContentDimensionInterface[] $contentDimensions
     */
    protected function createContentDimensionCollectionInstance(
        array $contentDimensions,
        DimensionCollectionInterface $dimensionCollection
    ): ContentDimensionCollectionInterface {
        return new ContentDimensionCollection($contentDimensions, $dimensionCollection);
    }

    public function testGetUnlocalizedDimension(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $contentDimensionCollection = $this->createContentDimensionCollectionInstance([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $this->assertSame(
            $contentDimension1->reveal(),
            $contentDimensionCollection->getUnlocalizedContentDimension()
        );
    }

    public function testGetLocalizedDimension(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $contentDimensionCollection = $this->createContentDimensionCollectionInstance([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $this->assertSame(
            $contentDimension2->reveal(),
            $contentDimensionCollection->getLocalizedContentDimension()
        );
    }

    public function testCount(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $contentDimensionCollection = $this->createContentDimensionCollectionInstance([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $this->assertCount(2, $contentDimensionCollection);
    }

    public function testIterator(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $contentDimensionCollection = $this->createContentDimensionCollectionInstance([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], $dimensionCollection);

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }
}
