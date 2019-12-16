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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class DimensionContentCollectionTest extends TestCase
{
    /**
     * @param DimensionContentInterface[] $dimensionContents
     */
    protected function createDimensionContentCollectionInstance(
        array $dimensionContents,
        DimensionCollectionInterface $dimensionCollection
    ): DimensionContentCollectionInterface {
        return new DimensionContentCollection($dimensionContents, $dimensionCollection);
    }

    public function testGetUnlocalizedDimension(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $this->assertSame(
            $dimensionContent1->reveal(),
            $dimensionContentCollection->getUnlocalizedDimensionContent()
        );
    }

    public function testGetLocalizedDimension(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $this->assertSame(
            $dimensionContent2->reveal(),
            $dimensionContentCollection->getLocalizedDimensionContent()
        );
    }

    public function testCount(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $this->assertCount(2, $dimensionContentCollection);
    }

    public function testIterator(): void
    {
        $dimension1 = new Dimension('123-456');
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $dimensionCollection = new DimensionCollection(
            ['locale' => 'de'],
            [
                $dimension1,
                $dimension2,
            ]
        );

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $this->assertSame([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], iterator_to_array($dimensionContentCollection));
    }
}
