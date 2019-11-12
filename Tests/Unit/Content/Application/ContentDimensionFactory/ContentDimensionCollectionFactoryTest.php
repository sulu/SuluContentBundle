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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDimensionFactory;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\ContentDimensionCollectionFactory;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\MapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollection;

class ContentDimensionCollectionFactoryTest extends TestCase
{
    protected function createContentDimensionCollectionFactoryInstance(iterable $mappers): ContentDimensionCollectionFactory
    {
        return new ContentDimensionCollectionFactory($mappers);
    }

    public function testCreateWithoutMapperExistContentDimension(): void
    {
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimensionId()->willReturn('123-456');
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimensionId()->willReturn('456-789');

        $content = $this->prophesize(ContentInterface::class);
        $content->getDimensions()->willReturn(new ArrayCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]));

        $attributes = [
            'locale' => 'de',
            'workflowStage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance([]);
        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }

    public function testCreateWithoutUnlocalizedContentDimension(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The "$dimensionCollection" should contain atleast a unlocalizedDimension.');

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimensionId()->willReturn('123-456');
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimensionId()->willReturn('456-789');

        $content = $this->prophesize(ContentInterface::class);
        $content->getDimensions()->willReturn(new ArrayCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]));

        $attributes = [
            'locale' => 'de',
            'workflowStage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance([]);
        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );
    }

    public function testCreateWithoutMapperNotExistContentDimension(): void
    {
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimensionId()->willReturn('123-456');
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimensionId()->willReturn('456-789');

        $content = $this->prophesize(ContentInterface::class);
        $content->getDimensions()->willReturn(new ArrayCollection([
            $contentDimension1->reveal(),
        ]));

        $content->createDimension('456-789')->shouldBeCalled()->willReturn($contentDimension2->reveal());
        $content->addDimension($contentDimension2->reveal())->shouldBeCalled();

        $attributes = [
            'locale' => 'de',
            'workflowStage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance([]);
        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }

    public function testCreateWithMappers(): void
    {
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimensionId()->willReturn('123-456');
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimensionId()->willReturn('456-789');

        $content = $this->prophesize(ContentInterface::class);
        $content->getDimensions()->willReturn(new ArrayCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]));

        $attributes = [
            'locale' => 'de',
            'workflowStage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $mapper1 = $this->prophesize(MapperInterface::class);
        $mapper1->map($data, $contentDimension1->reveal(), $contentDimension2->reveal())->shouldBeCalled();
        $mapper2 = $this->prophesize(MapperInterface::class);
        $mapper2->map($data, $contentDimension1->reveal(), $contentDimension2->reveal())->shouldBeCalled();

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance([
            $mapper1->reveal(),
            $mapper2->reveal(),
        ]);

        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }
}
