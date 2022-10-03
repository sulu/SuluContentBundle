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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DimensionContentCollectionFactoryTest extends TestCase
{
    /**
     * @param mixed[] $dimensionAttributes
     * @param DimensionContentInterface[] $existDimensionContents
     */
    protected function createDimensionContentCollectionFactoryInstance(
        array $dimensionAttributes,
        array $existDimensionContents,
        ContentDataMapperInterface $contentDataMapper
    ): DimensionContentCollectionFactory {
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load(Argument::any(), Argument::any())->willReturn(
            new DimensionContentCollection(
                $existDimensionContents,
                $dimensionAttributes,
                ExampleDimensionContent::class
            )
        );

        return new DimensionContentCollectionFactory(
            $dimensionContentRepository->reveal(),
            $contentDataMapper,
            new PropertyAccessor()
        );
    }

    public function testCreateWithExistingDimensionContent(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map(
            $data,
            Argument::that(
                function(DimensionContentCollectionInterface $collection) use ($dimensionContent1, $dimensionContent2) {
                    return [$dimensionContent1->reveal(), $dimensionContent2->reveal()] === \iterator_to_array($collection);
                }
            )
        )->shouldBeCalled();

        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
                $dimensionContent1->reveal(),
                $dimensionContent2->reveal(),
            ],
            $contentDataMapper->reveal()
        );

        $dimensionContentCollection = $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $attributes,
            $data
        );

        $this->assertCount(2, $dimensionContentCollection);
        $this->assertSame(ExampleDimensionContent::class, $dimensionContentCollection->getDimensionContentClass());
        $this->assertSame($attributes, $dimensionContentCollection->getDimensionAttributes());
        $this->assertSame(
            [$dimensionContent1->reveal(), $dimensionContent2->reveal()],
            \iterator_to_array($dimensionContentCollection)
        );
    }

    public function testCreateWithoutExistingDimensionContent(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->setLocale(null)
            ->shouldBeCalled();
        $dimensionContent1->setStage('draft')
            ->shouldBeCalled();
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->setLocale('de')
            ->shouldBeCalled();
        $dimensionContent2->setStage('draft')
            ->shouldBeCalled();
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->createDimensionContent()->shouldBeCalledTimes(2)
            ->willReturn($dimensionContent1->reveal(), $dimensionContent2->reveal());
        $contentRichEntity->addDimensionContent($dimensionContent1->reveal())->shouldBeCalled();
        $contentRichEntity->addDimensionContent($dimensionContent2->reveal())->shouldBeCalled();

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map(
            $data,
            Argument::that(
                function(DimensionContentCollectionInterface $collection) use ($dimensionContent1, $dimensionContent2) {
                    return [$dimensionContent1->reveal(), $dimensionContent2->reveal()] === \iterator_to_array($collection);
                }
            )
        )->shouldBeCalled();

        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
            ],
            $contentDataMapper->reveal()
        );

        $dimensionContentCollection = $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $attributes,
            $data
        );

        $this->assertCount(2, $dimensionContentCollection);
        $this->assertSame(ExampleDimensionContent::class, $dimensionContentCollection->getDimensionContentClass());
        $this->assertSame($attributes, $dimensionContentCollection->getDimensionAttributes());
        $this->assertSame(
            [$dimensionContent1->reveal(), $dimensionContent2->reveal()],
            \iterator_to_array($dimensionContentCollection)
        );
    }

    public function testCreateWithoutExistingLocalizedDimensionContent(): void
    {
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getLocale()->willReturn(null);
        $dimensionContent1->getStage()->willReturn('draft');
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->setLocale('de')
            ->shouldBeCalled();
        $dimensionContent2->setStage('draft')
            ->shouldBeCalled();
        $dimensionContent2->getLocale()->willReturn('de');
        $dimensionContent2->getStage()->willReturn('draft');

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->createDimensionContent()->shouldBeCalled()->willReturn($dimensionContent2->reveal());
        $contentRichEntity->addDimensionContent($dimensionContent2->reveal())->shouldBeCalled();

        $attributes = [
            'locale' => 'de',
            'stage' => 'draft',
        ];

        $data = [
            'data' => 'value',
        ];

        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);
        $contentDataMapper->map(
            $data,
            Argument::that(
                function(DimensionContentCollectionInterface $collection) use ($dimensionContent1, $dimensionContent2) {
                    return [$dimensionContent1->reveal(), $dimensionContent2->reveal()] === \iterator_to_array($collection);
                }
            )
        )->shouldBeCalled();
        $dimensionContentCollectionFactoryInstance = $this->createDimensionContentCollectionFactoryInstance(
            $attributes,
            [
                $dimensionContent1->reveal(),
            ],
            $contentDataMapper->reveal()
        );

        $dimensionContentCollection = $dimensionContentCollectionFactoryInstance->create(
            $contentRichEntity->reveal(),
            $attributes,
            $data
        );

        $this->assertCount(2, $dimensionContentCollection);
        $this->assertSame(ExampleDimensionContent::class, $dimensionContentCollection->getDimensionContentClass());
        $this->assertSame($attributes, $dimensionContentCollection->getDimensionAttributes());
        $this->assertSame(
            [$dimensionContent1->reveal(), $dimensionContent2->reveal()],
            \iterator_to_array($dimensionContentCollection)
        );
    }
}
