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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\ViewFactory;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ViewFactoryTest extends TestCase
{
    protected function getViewFactoryInstance(iterable $mergers = []): ViewFactoryInterface
    {
        return new ViewFactory($mergers);
    }

    public function testCreateEmpty(): void
    {
        $this->expectException(\RuntimeException::class);

        $viewFactory = $this->getViewFactoryInstance();
        $viewFactory->create(new DimensionContentCollection([], new DimensionCollection([], [])));
    }

    public function testCreate(): void
    {
        $contentViewDimension1 = $this->prophesize(DimensionContentInterface::class);
        $contentViewDimension2 = $this->prophesize(DimensionContentInterface::class);
        $contentViewDimension3 = $this->prophesize(DimensionContentInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);

        $contentViewDimension3->createViewInstance()->willReturn($contentView->reveal())->shouldBeCalled();

        $viewFactory = $this->getViewFactoryInstance();
        $viewFactory->create(new DimensionContentCollection([
            $contentViewDimension1->reveal(),
            $contentViewDimension2->reveal(),
            $contentViewDimension3->reveal(),
        ], new DimensionCollection([], [])));
    }

    public function testCreateMergers(): void
    {
        $contentViewDimension1 = $this->prophesize(DimensionContentInterface::class);
        $contentViewDimension2 = $this->prophesize(DimensionContentInterface::class);
        $contentViewDimension3 = $this->prophesize(DimensionContentInterface::class);

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger1 = $this->prophesize(MergerInterface::class);
        $merger1->merge($contentView, $contentViewDimension1)->shouldBeCalled();
        $merger1->merge($contentView, $contentViewDimension2)->shouldBeCalled();
        $merger1->merge($contentView, $contentViewDimension3)->shouldBeCalled();

        $merger2 = $this->prophesize(MergerInterface::class);
        $merger2->merge($contentView, $contentViewDimension1)->shouldBeCalled();
        $merger2->merge($contentView, $contentViewDimension2)->shouldBeCalled();
        $merger2->merge($contentView, $contentViewDimension3)->shouldBeCalled();

        $merger3 = $this->prophesize(MergerInterface::class);
        $merger3->merge($contentView, $contentViewDimension1)->shouldBeCalled();
        $merger3->merge($contentView, $contentViewDimension2)->shouldBeCalled();
        $merger3->merge($contentView, $contentViewDimension3)->shouldBeCalled();

        $contentViewDimension3->createViewInstance()->willReturn($contentView->reveal())->shouldBeCalled();

        $viewFactory = $this->getViewFactoryInstance([
            $merger1->reveal(),
            $merger2->reveal(),
            $merger3->reveal(),
        ]);

        $viewFactory->create(new DimensionContentCollection([
            $contentViewDimension1->reveal(),
            $contentViewDimension2->reveal(),
            $contentViewDimension3->reveal(),
        ], new DimensionCollection([], [])));
    }
}
