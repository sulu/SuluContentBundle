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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentProjection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractDimensionContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class DimensionContentTest extends TestCase
{
    protected function getDimensionContentInstance(): DimensionContentInterface
    {
        $dimension = new Dimension('123-456');

        return new class($dimension) extends AbstractDimensionContent {
            protected $id = 1;

            public function __construct(DimensionInterface $dimension)
            {
                $this->dimension = $dimension;
            }

            public function createProjectionInstance(): ContentProjectionInterface
            {
                return new class() extends AbstractContentProjection {
                    public function getContentId()
                    {
                        return 5;
                    }
                };
            }
        };
    }

    public function testGetId(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertSame(1, $model->getId());
    }

    public function testGetDimension(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertSame('123-456', $model->getDimension()->getId());
    }
}
