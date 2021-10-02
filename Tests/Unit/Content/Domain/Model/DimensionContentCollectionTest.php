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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

class DimensionContentCollectionTest extends TestCase
{
    /**
     * @param DimensionContentInterface[] $dimensionContents
     * @param mixed[] $dimensionAttributes
     */
    protected function createDimensionContentCollectionInstance(
        array $dimensionContents,
        array $dimensionAttributes
    ): DimensionContentCollectionInterface {
        return new DimensionContentCollection($dimensionContents, $dimensionAttributes, ExampleDimensionContent::class);
    }

    public function testCount(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertCount(2, $dimensionContentCollection);
        $this->assertSame(2, \count($dimensionContentCollection)); // @phpstan-ignore-line
        $this->assertSame(2, $dimensionContentCollection->count()); // @phpstan-ignore-line
    }

    public function testSortedByAttributes(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent2->reveal(),
            $dimensionContent1->reveal(),
        ], $attributes);

        $this->assertSame([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], \iterator_to_array($dimensionContentCollection));
    }

    public function testIterator(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertSame([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], \iterator_to_array($dimensionContentCollection));
    }

    public function testGetDimensionContentClass(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertSame(
            ExampleDimensionContent::class,
            $dimensionContentCollection->getDimensionContentClass()
        );
    }

    public function testGetDimensionAttributes(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = [
            'locale' => 'de',
        ];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertSame(
            [
                'locale' => 'de',
                'stage' => 'draft',
                'version' => 0,
            ],
            $dimensionContentCollection->getDimensionAttributes()
        );
    }

    public function testGetDimensionContent(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertSame(
            $dimensionContent2->reveal(),
            $dimensionContentCollection->getDimensionContent($attributes)
        );
    }

    public function testGetDimensionContentNotExist(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent1->getVersion()->willReturn(0);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');
        $dimensionContent2->getVersion()->willReturn(0);

        $attributes = ['locale' => 'de'];

        $dimensionContentCollection = $this->createDimensionContentCollectionInstance([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $attributes);

        $this->assertNull($dimensionContentCollection->getDimensionContent(['locale' => 'en']));
    }
}
