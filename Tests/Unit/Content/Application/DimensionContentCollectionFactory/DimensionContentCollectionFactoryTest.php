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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\DimensionContentCollectionFactory;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentCollectionFactory\DimensionContentCollectionFactory;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;

class DimensionContentCollectionFactoryTest extends TestCase
{
    /**
     * @param mixed[] $dimensionAttributes
     * @param DimensionInterface[] $existDimensions
     * @param DimensionContentInterface[] $existDimensionContents
     */
    protected function createDimensionContentCollectionFactoryInstance(
        array $dimensionAttributes,
        array $existDimensions,
        array $existDimensionContents,
        ContentDataMapperInterface $contentDataMapper
    ): DimensionContentCollectionFactory {
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load(Argument::any(), Argument::any())->willReturn(
            new DimensionContentCollection(
                $existDimensionContents,
                new DimensionCollection($dimensionAttributes, $existDimensions)
            )
        );

        return new DimensionContentCollectionFactory($dimensionContentRepository->reveal(), $contentDataMapper);
    }

    public function testCreateWithExistingDimensionContent(): void
    {
        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map($data, $dimensionContent1->reveal(), $dimensionContent2->reveal())->shouldBeCalled();

        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
                $dimension1,
                $dimension2,
            ],
            [
                $dimensionContent1->reveal(),
                $dimensionContent2->reveal(),
            ],
            $contentDataMapper->reveal()
        );

        $dimensionContentCollection = $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertSame([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], iterator_to_array($dimensionContentCollection));
    }

    public function testCreateWithoutUnlocalizedDimensionContent(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The "$dimensionCollection" should contain atleast a unlocalizedDimension.');

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map(Argument::cetera())->shouldNotBeCalled();

        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension2]);

        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
                $dimension1,
                $dimension2,
            ],
            [
                $dimensionContent1->reveal(),
                $dimensionContent2->reveal(),
            ],
            $contentDataMapper->reveal()
        );
        $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $dimensionCollection,
            $data
        );
    }

    public function testCreateWithoutExistingDimensionContent(): void
    {
        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->createDimensionContent($dimension2)->shouldBeCalled()->willReturn($dimensionContent2->reveal());
        $contentRichEntity->addDimensionContent($dimensionContent2->reveal())->shouldBeCalled();

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map($data, $dimensionContent1->reveal(), $dimensionContent2->reveal())->shouldBeCalled();

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
                $dimension1,
                $dimension2,
            ],
            [
                $dimensionContent1->reveal(),
            ],
            $contentDataMapper->reveal()
        );

        $dimensionContentCollection = $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $dimensionCollection,
            $data
        );

        $this->assertCount(2, $dimensionContentCollection);

        $this->assertSame([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], iterator_to_array($dimensionContentCollection));
    }
}
