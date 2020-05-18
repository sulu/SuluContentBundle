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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentRichEntityTraitTest extends TestCase
{
    protected function getContentRichEntityInstance(): ContentRichEntityInterface
    {
        return new class() implements ContentRichEntityInterface {
            use ContentRichEntityTrait;

            public static function getResourceKey(): string
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }

            public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }

            public function getId()
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }
        };
    }

    public function testGetAddRemoveDimension(): void
    {
        $model = $this->getContentRichEntityInstance();

        $this->assertEmpty($model->getDimensionContents());

        $modelDimension1 = $this->prophesize(DimensionContentInterface::class);
        $modelDimension2 = $this->prophesize(DimensionContentInterface::class);

        $model->addDimensionContent($modelDimension1->reveal());
        $model->addDimensionContent($modelDimension2->reveal());

        $this->assertSame([
            $modelDimension1->reveal(),
            $modelDimension2->reveal(),
        ], iterator_to_array($model->getDimensionContents()));

        $model->removeDimensionContent($modelDimension2->reveal());

        $this->assertSame([
            $modelDimension1->reveal(),
        ], iterator_to_array($model->getDimensionContents()));
    }
}
