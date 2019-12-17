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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory\Merger;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\TemplateMerger;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
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

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(TemplateInterface::class);
        $contentView->setTemplateKey(Argument::any())->shouldNotBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeViewNotImplementTemplateInterface(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey(Argument::any())->shouldNotBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergeSet(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey()->willReturn('template-key')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn(['template' => 'data'])->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(TemplateInterface::class);
        $contentView->setTemplateKey('template-key')->shouldBeCalled();
        $contentView->getTemplateData()->willReturn(['template2' => 'data2'])->shouldBeCalled();
        $contentView->setTemplateData(['template' => 'data', 'template2' => 'data2'])->shouldBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }

    public function testMergNotSet(): void
    {
        $merger = $this->getTemplateMergerInstance();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateKey()->willReturn('')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $contentView->willImplement(TemplateInterface::class);
        $contentView->setTemplateKey('')->shouldNotBeCalled();
        $contentView->getTemplateData()->willReturn(['template2' => 'data2'])->shouldBeCalled();
        $contentView->setTemplateData(['template2' => 'data2'])->shouldBeCalled();

        $merger->merge($contentView->reveal(), $dimensionContent->reveal());
    }
}
