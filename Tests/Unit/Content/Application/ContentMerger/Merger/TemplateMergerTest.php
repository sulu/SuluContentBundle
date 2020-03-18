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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentMerger\Merger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\TemplateMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateMergerTest extends TestCase
{
    protected function getTemplateMergerInstance(): MergerInterface
    {
        return new TemplateMerger();
    }

    public function testMergeDimensionNotImplementTemplateInterface(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(TemplateInterface::class);
        $contentProjection->setTemplateKey(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementTemplateInterface(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey(Argument::any())->shouldNotBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey()->willReturn('template-key')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn(['template' => 'data'])->shouldBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(TemplateInterface::class);
        $contentProjection->setTemplateKey('template-key')->shouldBeCalled();
        $contentProjection->getTemplateData()->willReturn(['template2' => 'data2'])->shouldBeCalled();
        $contentProjection->setTemplateData(['template' => 'data', 'template2' => 'data2'])->shouldBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }

    public function testMergNotSet(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey()->willReturn('')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();

        $contentProjection = $this->prophesize(ContentProjectionInterface::class);
        $contentProjection->willImplement(TemplateInterface::class);
        $contentProjection->setTemplateKey('')->shouldNotBeCalled();
        $contentProjection->getTemplateData()->willReturn(['template2' => 'data2'])->shouldBeCalled();
        $contentProjection->setTemplateData(['template2' => 'data2'])->shouldBeCalled();

        $merger->merge($contentProjection->reveal(), $dimensionContent->reveal());
    }
}
