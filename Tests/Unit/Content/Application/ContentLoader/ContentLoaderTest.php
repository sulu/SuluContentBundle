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
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentLoaderTest extends TestCase
{
    protected function createContentLoaderInstance(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ): ContentLoaderInterface {
        return new ContentLoader(
            $dimensionRepository,
            $contentDimensionRepository,
            $viewFactory,
            $viewResolver
        );
    }

    protected function createContentInstance(): ContentInterface
    {
        return new class() extends AbstractContent {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimension(DimensionInterface $dimension): ContentDimensionInterface
            {
                throw new \RuntimeException('Should not be called in a unit test.');
            }

            public function getId()
            {
                return null;
            }
        };
    }

    public function testLoad(): void
    {
        $dimension1 = $this->prophesize(DimensionInterface::class);
        $dimension1->getId()->willReturn('123-456');
        $dimension2 = $this->prophesize(DimensionInterface::class);
        $dimension2->getId()->willReturn('456-789');

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimension()->willReturn($dimension1->reveal());
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimension()->willReturn($dimension2->reveal());

        $content = $this->prophesize(ContentInterface::class);

        $attributes = [
            'locale' => 'de',
        ];

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]);

        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);
        $contentDimensionRepository->load($content->reveal(), $dimensionCollection)->willReturn($contentDimensionCollection);
        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($contentDimensionCollection)->willReturn($contentView->reveal())->shouldBeCalled();

        $viewResolver = $this->prophesize(ApiViewResolverInterface::class);
        $viewResolver->resolve($contentView->reveal())->willReturn(['resolved' => 'data'])->shouldBeCalled();

        $createContentMessageHandler = $this->createContentLoaderInstance(
            $dimensionRepository->reveal(),
            $contentDimensionRepository->reveal(),
            $viewFactory->reveal(),
            $viewResolver->reveal()
        );

        $this->assertSame(['resolved' => 'data'], $createContentMessageHandler->load($content->reveal(), $attributes));
    }
}
