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

    public function testMap(): void
    {
        $merger1 = $this->prophesize(MergerInterface::class);
        $merger2 = $this->prophesize(MergerInterface::class);

        $contentMerger = $this->createContentMergerInstance([
            $merger1->reveal(),
            $merger2->reveal(),
        ]);

        $targetObject = $this->prophesize(\stdClass::class);
        $sourceObject = $this->prophesize(\stdClass::class);

        $merger1->merge($targetObject->reveal(), $sourceObject->reveal())->shouldBeCalled();
        $merger2->merge($targetObject->reveal(), $sourceObject->reveal())->shouldBeCalled();

        $contentMerger->merge($targetObject->reveal(), $sourceObject->reveal());
    }
}
