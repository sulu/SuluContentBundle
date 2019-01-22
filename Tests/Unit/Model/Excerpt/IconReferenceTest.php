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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReference;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class IconReferenceTest extends TestCase
{
    public function testGetExcerptDimension(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $iconReference = new IconReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($excerptDimension->reveal(), $iconReference->getExcerptDimension());
    }

    public function testGetMedia(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $iconReference = new IconReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($media->reveal(), $iconReference->getMedia());
    }

    public function testGetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $iconReference = new IconReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame(2, $iconReference->getOrder());
    }

    public function testSetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $iconReference = new IconReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($iconReference, $iconReference->setOrder(5));
        $this->assertSame(5, $iconReference->getOrder());
    }
}
