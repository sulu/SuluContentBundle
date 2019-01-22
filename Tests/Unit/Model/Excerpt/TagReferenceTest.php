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
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReference;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class TagReferenceTest extends TestCase
{
    public function testGetExcerptDimension(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $tag = $this->prophesize(TagInterface::class);
        $tagReference = new TagReference($excerptDimension->reveal(), $tag->reveal(), 2);

        $this->assertSame($excerptDimension->reveal(), $tagReference->getExcerptDimension());
    }

    public function testGetTag(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $tag = $this->prophesize(TagInterface::class);
        $tagReference = new TagReference($excerptDimension->reveal(), $tag->reveal(), 2);

        $this->assertSame($tag->reveal(), $tagReference->getTag());
    }

    public function testGetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $tag = $this->prophesize(TagInterface::class);
        $tagReference = new TagReference($excerptDimension->reveal(), $tag->reveal(), 2);

        $this->assertSame(2, $tagReference->getOrder());
    }

    public function testSetOrder(): void
    {
        $excerptDimension = $this->prophesize(ExcerptDimensionInterface::class);
        $tag = $this->prophesize(TagInterface::class);
        $tagReference = new TagReference($excerptDimension->reveal(), $tag->reveal(), 2);

        $this->assertSame($tagReference, $tagReference->setOrder(5));
        $this->assertSame(5, $tagReference->getOrder());
    }
}
