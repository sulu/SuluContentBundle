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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentDimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentDimensionTest extends TestCase
{
    protected function getContentDimensionInstance(): ContentDimensionInterface
    {
        $dimension = new Dimension('123-456');

        return new class($dimension) extends AbstractContentDimension {
            protected $id = 1;

            public function __construct(DimensionInterface $dimension)
            {
                $this->dimension = $dimension;
            }

            public function createViewInstance(): ContentViewInterface
            {
                return new class() extends AbstractContentView {
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
        $model = $this->getContentDimensionInstance();
        $this->assertSame(1, $model->getId());
    }

    public function testGetDimension(): void
    {
        $model = $this->getContentDimensionInstance();
        $this->assertSame('123-456', $model->getDimension()->getId());
    }
}
