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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;

class ContentProjectionTest extends TestCase
{
    protected function getContentProjectionInstance(): ContentProjectionInterface
    {
        return new class() implements ContentProjectionInterface {
            use ContentProjectionTrait;

            public function __construct()
            {
                $this->dimension = new Dimension('123-456');
            }

            public function getContentId()
            {
                return 5;
            }
        };
    }

    public function testGetDimensionId(): void
    {
        $model = $this->getContentProjectionInstance();
        $this->assertSame('123-456', $model->getDimension()->getId());
    }

    public function testGetContentId(): void
    {
        $model = $this->getContentProjectionInstance();
        $this->assertSame(5, $model->getContentId());
    }
}
