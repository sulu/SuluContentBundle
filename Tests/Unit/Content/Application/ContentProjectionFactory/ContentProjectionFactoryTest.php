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
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\ContentProjectionFactory;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentProjectionFactoryTest extends TestCase
{
    protected function getViewFactoryInstance(
        ContentMergerInterface $contentMerger
    ): ContentProjectionFactoryInterface {
        return new ContentProjectionFactory($contentMerger);
    }

    public function testCreateEmpty(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected at least one dimensionContent given.');

        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentMerger->merge(Argument::cetera())->shouldNotBeCalled();

        $viewFactory = $this->getViewFactoryInstance($contentMerger->reveal());
        $viewFactory->create(new DimensionContentCollection([], new DimensionCollection([], [])));
    }

    public function testCreate(): void
    {
        $contentProjectionDimension1 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension2 = $this->prophesize(DimensionContentInterface::class);
        $contentProjectionDimension3 = $this->prophesize(DimensionContentInterface::class);

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $contentProjectionDimension3->createProjectionInstance()->willReturn($contentProjection->reveal())->shouldBeCalled();

        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentMerger->merge($contentProjection->reveal(), $contentProjectionDimension3->reveal())->shouldBeCalled();
        $contentMerger->merge($contentProjection->reveal(), $contentProjectionDimension2->reveal())->shouldBeCalled();
        $contentMerger->merge($contentProjection->reveal(), $contentProjectionDimension1->reveal())->shouldBeCalled();

        $viewFactory = $this->getViewFactoryInstance($contentMerger->reveal());
        $viewFactory->create(new DimensionContentCollection([
            $contentProjectionDimension1->reveal(),
            $contentProjectionDimension2->reveal(),
            $contentProjectionDimension3->reveal(),
        ], new DimensionCollection([], [])));
    }
}
