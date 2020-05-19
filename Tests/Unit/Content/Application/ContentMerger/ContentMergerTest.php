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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentMerger;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMerger;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentMergerTest extends TestCase
{
    /**
     * @param iterable<MergerInterface> $mergers
     */
    protected function createContentMergerInstance(
        iterable $mergers
    ): ContentMergerInterface {
        return new ContentMerger($mergers);
    }

    public function testMerge(): void
    {
        $merger1 = $this->prophesize(MergerInterface::class);
        $merger2 = $this->prophesize(MergerInterface::class);

        $contentMerger = $this->createContentMergerInstance([
            $merger1->reveal(),
            $merger2->reveal(),
        ]);

        $mergedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent1 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent2 = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent3 = $this->prophesize(DimensionContentInterface::class);

        $mostSpecificDimension = $this->prophesize(DimensionInterface::class);

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->createDimensionContent($mostSpecificDimension->reveal())
            ->willReturn($mergedDimensionContent->reveal());

        $dimensionContent3->getDimension()->willReturn($mostSpecificDimension->reveal());
        $dimensionContent3->getContentRichEntity()->willReturn($contentRichEntity->reveal());

        $merger1->merge($mergedDimensionContent->reveal(), $dimensionContent1->reveal())->shouldBeCalled();
        $merger2->merge($mergedDimensionContent->reveal(), $dimensionContent1->reveal())->shouldBeCalled();

        $merger1->merge($mergedDimensionContent->reveal(), $dimensionContent2->reveal())->shouldBeCalled();
        $merger2->merge($mergedDimensionContent->reveal(), $dimensionContent2->reveal())->shouldBeCalled();

        $merger1->merge($mergedDimensionContent->reveal(), $dimensionContent3->reveal())->shouldBeCalled();
        $merger2->merge($mergedDimensionContent->reveal(), $dimensionContent3->reveal())->shouldBeCalled();

        $mergedDimensionContent->markAsMerged()->shouldBeCalled();

        $dimensionContentCollection = new DimensionContentCollection([
            $dimensionContent1->reveal(),
            $dimensionContent2->reveal(),
            $dimensionContent3->reveal(),
        ], new DimensionCollection([], []));

        $this->assertSame(
            $mergedDimensionContent->reveal(),
            $contentMerger->merge($dimensionContentCollection)
    );
    }

    public function testMergeEmptyCollection(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected at least one dimensionContent given.');

        $merger1 = $this->prophesize(MergerInterface::class);
        $merger2 = $this->prophesize(MergerInterface::class);

        $contentMerger = $this->createContentMergerInstance([
            $merger1->reveal(),
            $merger2->reveal(),
        ]);

        $dimensionContentCollection = new DimensionContentCollection([], new DimensionCollection([], []));

        $contentMerger->merge($dimensionContentCollection);
    }
}
