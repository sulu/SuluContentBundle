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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentPersister;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersister;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentPersisterTest extends TestCase
{
    protected function createContentPersisterInstance(
        DimensionCollectionFactoryInterface $dimensionCollectionFactory,
        DimensionContentCollectionFactoryInterface $dimensionContentCollectionFactory,
        ContentMergerInterface $contentMerger
    ): ContentPersisterInterface {
        return new ContentPersister(
            $dimensionCollectionFactory,
            $dimensionContentCollectionFactory,
            $contentMerger
        );
    }

    protected function createContentRichEntityInstance(): ContentRichEntityInterface
    {
        return new class() implements ContentRichEntityInterface {
            use ContentRichEntityTrait;

            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
            {
                throw new \RuntimeException('Should not be called in a unit test.');
            }

            public function getId()
            {
                return null;
            }
        };
    }

    public function testPersist(): void
    {
        $contentRichEntity = $this->createContentRichEntityInstance();
        $attributes = [
            'locale' => 'de',
        ];
        $data = [
            'data' => 'value',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => 'de']);
        $dimension2 = new Dimension('456-789', ['locale' => null]);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension2, $dimension1]);

        $dimensionCollectionFactory = $this->prophesize(DimensionCollectionFactoryInterface::class);
        $dimensionCollectionFactory->create($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2);
        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], $dimensionCollection);

        $dimensionContentCollectionFactory = $this->prophesize(DimensionContentCollectionFactoryInterface::class);
        $dimensionContentCollectionFactory->create($contentRichEntity, $dimensionCollection, $data)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentMerger->mergeCollection($dimensionContentCollection)->willReturn($resolvedContent->reveal())->shouldBeCalled();

        $createContentMessageHandler = $this->createContentPersisterInstance(
            $dimensionCollectionFactory->reveal(),
            $dimensionContentCollectionFactory->reveal(),
            $contentMerger->reveal()
        );

        $this->assertSame($resolvedContent->reveal(), $createContentMessageHandler->persist($contentRichEntity, $data, $attributes));
    }
}
