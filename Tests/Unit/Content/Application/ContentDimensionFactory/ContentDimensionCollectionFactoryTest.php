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

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\ContentDimensionCollectionFactory;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\MapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;

class ContentDimensionCollectionFactoryTest extends TestCase
{
    /**
     * @param ContentDimensionInterface[] $existContentDimensions
     */
    protected function createContentDimensionCollectionFactoryInstance(array $existContentDimensions, iterable $mappers): ContentDimensionCollectionFactory
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $contentDimensionRepository->load(Argument::any(), Argument::any())->willReturn(
            new ContentDimensionCollection($existContentDimensions)
        );

        return new ContentDimensionCollectionFactory($contentDimensionRepository->reveal(), $mappers);
    }

    public function testCreateWithoutMapperExistContentDimension(): void
    {
        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $content = $this->prophesize(ContentInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance(
            [
                $contentDimension1->reveal(),
                $contentDimension2->reveal(),
            ],
            []
        );
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

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $content = $this->prophesize(ContentInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance(
            [
                $contentDimension1->reveal(),
                $contentDimension2->reveal(),
            ],
            []
        );
        $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );
    }

    public function testCreateWithoutMapperNotExistContentDimension(): void
    {
        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $content = $this->prophesize(ContentInterface::class);
        $content->createDimension($dimension2)->shouldBeCalled()->willReturn($contentDimension2->reveal());
        $content->addDimension($contentDimension2->reveal())->shouldBeCalled();

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance(
            [
                $contentDimension1->reveal(),
            ],
            []
        );

        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertCount(2, $contentDimensionCollection);

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }

    public function testCreateWithMappers(): void
    {
        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2);

        $content = $this->prophesize(ContentInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $mapper1 = $this->prophesize(MapperInterface::class);
        $mapper1->map($data, $contentDimension1->reveal(), $contentDimension2->reveal())->shouldBeCalled();
        $mapper2 = $this->prophesize(MapperInterface::class);
        $mapper2->map($data, $contentDimension1->reveal(), $contentDimension2->reveal())->shouldBeCalled();

        $contentDimensionCollectionFactoryInstance = $this->createContentDimensionCollectionFactoryInstance(
            [
                $contentDimension1->reveal(),
                $contentDimension2->reveal(),
            ],
            [
                $mapper1->reveal(),
                $mapper2->reveal(),
            ]
        );

        $contentDimensionCollection = $contentDimensionCollectionFactoryInstance->create(
            $content->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertCount(2, $contentDimensionCollection);

        $this->assertSame([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ], iterator_to_array($contentDimensionCollection));
    }
}
