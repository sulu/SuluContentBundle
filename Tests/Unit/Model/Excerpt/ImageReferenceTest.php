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
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReference;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;

class ImageReferenceTest extends TestCase
{
    public function testCreateClone(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $newExcerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $newIconReference = $imageReference->createClone($newExcerptDimension->reveal());

        $this->assertSame($newExcerptDimension->reveal(), $newIconReference->getExcerptDimension());
    }

    public function testGetExcerptDimension(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($excerptDimension->reveal(), $imageReference->getExcerptDimension());
    }

    public function testGetMedia(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($media->reveal(), $imageReference->getMedia());
    }

    public function testGetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame(2, $imageReference->getOrder());
    }

    public function testSetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertSame($imageReference, $imageReference->setOrder(5));
        $this->assertSame(5, $imageReference->getOrder());
    }
}
