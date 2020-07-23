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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class DimensionContentTraitTest extends TestCase
{
    protected function getDimensionContentInstance(
        DimensionInterface $dimension
    ): DimensionContentInterface {
        return new class($dimension) implements DimensionContentInterface {
            use DimensionContentTrait;

            public function __construct(DimensionInterface $dimension)
            {
                $this->dimension = $dimension;
            }

            public static function getResourceKey(): string
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }

            public function getResource(): ContentRichEntityInterface
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }
        };
    }

    public function testGetDimension(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);

        $model = $this->getDimensionContentInstance($dimension->reveal());
        $this->assertSame($dimension->reveal(), $model->getDimension());
    }

    public function testGetSetIsMerged(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);

        $model = $this->getDimensionContentInstance($dimension->reveal());
        $this->assertFalse($model->isMerged());

        $model->markAsMerged();
        $this->assertTrue($model->isMerged());
    }
}
