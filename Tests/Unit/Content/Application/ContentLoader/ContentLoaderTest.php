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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentLoader;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoader;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentLoaderTest extends TestCase
{
    protected function createContentLoaderInstance(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ViewFactoryInterface $viewFactory
    ): ContentLoaderInterface {
        return new ContentLoader(
            $dimensionRepository,
            $dimensionContentRepository,
            $viewFactory
        );
    }

    public function testLoad(): void
    {
        $dimension1 = $this->prophesize(DimensionInterface::class);
        $dimension1->getId()->willReturn('123-456');
        $dimension2 = $this->prophesize(DimensionInterface::class);
        $dimension2->getId()->willReturn('456-789');

        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1->getDimension()->willReturn($dimension1->reveal());
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2->getDimension()->willReturn($dimension2->reveal());

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
        ], new DimensionCollection($attributes, [$dimension1, $dimension2]));

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)->willReturn($dimensionContentCollection);
        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($dimensionContentCollection)->willReturn($contentView->reveal())->shouldBeCalled();

        $createContentMessageHandler = $this->createContentLoaderInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $this->assertSame($contentView->reveal(), $createContentMessageHandler->load($contentRichEntity->reveal(), $attributes));
    }

    public function testLoadDimensionNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
        ];

        $dimensionCollection = new DimensionCollection($attributes, []);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);

        $createContentMessageHandler = $this->createContentLoaderInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $this->assertSame($contentView->reveal(), $createContentMessageHandler->load($contentRichEntity->reveal(), $attributes));
    }

    public function testLoadNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimension1 = $this->prophesize(DimensionInterface::class);
        $dimension1->getId()->willReturn('123-456');
        $dimension2 = $this->prophesize(DimensionInterface::class);
        $dimension2->getId()->willReturn('456-789');

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $attributes = [
            'locale' => 'de',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $dimensionContentCollection = new DimensionContentCollection(
            [],
            new DimensionCollection($attributes, [$dimension1, $dimension2])
        );

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)->willReturn($dimensionContentCollection);
        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($dimensionContentCollection)->willReturn($contentView->reveal())->shouldNotBeCalled();

        $createContentMessageHandler = $this->createContentLoaderInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $viewFactory->reveal()
        );

        $this->assertSame($contentView->reveal(), $createContentMessageHandler->load($contentRichEntity->reveal(), $attributes));
    }
}
