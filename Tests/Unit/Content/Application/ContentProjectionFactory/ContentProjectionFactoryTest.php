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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentProjectionFactory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\ContentProjectionFactory;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentProjectionFactoryTest extends TestCase
{
    protected function getViewFactoryInstance(iterable $mergers = []): ContentProjectionFactoryInterface
    {
        return new ContentProjectionFactory($mergers);
    }

    public function testCreateEmpty(): void
    {
        $this->expectException(\RuntimeException::class);

        $viewFactory = $this->getViewFactoryInstance();
        $viewFactory->create(new DimensionContentCollection([], new DimensionCollection([], [])));
    }

    public function testCreate(): void
    {
        $contentProjectionDimension1 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension2 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension3 = $this->prophesize(DimensionContentInterface::class);

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentProjectionDimension3->createProjectionInstance()->willReturn($contentProjection->reveal())->shouldBeCalled();

        $viewFactory = $this->getViewFactoryInstance();
        $viewFactory->create(new DimensionContentCollection([
            $contentProjectionDimension1->reveal(),
            $contentProjectionDimension2->reveal(),
            $contentProjectionDimension3->reveal(),
        ], new DimensionCollection([], [])));
    }

    public function testCreateMergers(): void
    {
        $contentProjectionDimension1 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension2 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension3 = $this->prophesize(DimensionContentInterface::class);

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $merger1 = $this->prophesize(MergerInterface::class);
        $merger1->merge($contentProjection, $contentProjectionDimension1)->shouldBeCalled();
        $merger1->merge($contentProjection, $contentProjectionDimension2)->shouldBeCalled();
        $merger1->merge($contentProjection, $contentProjectionDimension3)->shouldBeCalled();

        $merger2 = $this->prophesize(MergerInterface::class);
        $merger2->merge($contentProjection, $contentProjectionDimension1)->shouldBeCalled();
        $merger2->merge($contentProjection, $contentProjectionDimension2)->shouldBeCalled();
        $merger2->merge($contentProjection, $contentProjectionDimension3)->shouldBeCalled();

        $merger3 = $this->prophesize(MergerInterface::class);
        $merger3->merge($contentProjection, $contentProjectionDimension1)->shouldBeCalled();
        $merger3->merge($contentProjection, $contentProjectionDimension2)->shouldBeCalled();
        $merger3->merge($contentProjection, $contentProjectionDimension3)->shouldBeCalled();

        $contentProjectionDimension3->createProjectionInstance()->willReturn($contentProjection->reveal())->shouldBeCalled();

        $viewFactory = $this->getViewFactoryInstance([
            $merger1->reveal(),
            $merger2->reveal(),
            $merger3->reveal(),
        ]);

        $viewFactory->create(new DimensionContentCollection([
            $contentProjectionDimension1->reveal(),
            $contentProjectionDimension2->reveal(),
            $contentProjectionDimension3->reveal(),
        ], new DimensionCollection([], [])));
    }
}
