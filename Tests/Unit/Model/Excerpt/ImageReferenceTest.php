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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimension;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ImageReferenceTest extends TestCase
{
    public function testGetExcerptDimension(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertEquals($excerptDimension->reveal(), $imageReference->getExcerptDimension());
    }

    public function testGetMedia(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertEquals($media->reveal(), $imageReference->getMedia());
    }

    public function testGetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertEquals(2, $imageReference->getOrder());
    }

    public function testSetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $media = $this->prophesize(MediaInterface::class);
        $imageReference = new ImageReference($excerptDimension->reveal(), $media->reveal(), 2);

        $this->assertEquals($imageReference, $imageReference->setOrder(5));
        $this->assertEquals(5, $imageReference->getOrder());
    }
}
