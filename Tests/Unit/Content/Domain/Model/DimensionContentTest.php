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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class DimensionContentTest extends TestCase
{
    protected function getDimensionContentInstance(): DimensionContentInterface
    {
        $dimension = new Dimension('123-456');

        return new class($dimension) implements DimensionContentInterface {
            use DimensionContentTrait;

            public function __construct(DimensionInterface $dimension)
            {
                $this->dimension = $dimension;
            }

            public function getContentRichEntity(): ContentRichEntityInterface
            {
                return new class() implements ContentRichEntityInterface {
                    use ContentRichEntityTrait;

                    public static function getResourceKey(): string
                    {
                        throw new \RuntimeException();
                    }

                    public function getId()
                    {
                        throw new \RuntimeException();
                    }

                    public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
                    {
                        throw new \RuntimeException();
                    }
                };
            }
        };
    }

    public function testGetDimension(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertSame('123-456', $model->getDimension()->getId());
    }
}
