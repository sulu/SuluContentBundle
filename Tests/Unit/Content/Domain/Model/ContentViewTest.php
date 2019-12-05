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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentViewTest extends TestCase
{
    protected function getContentViewInstance(): ContentViewInterface
    {
        return new class() extends AbstractContentView {
            protected $id = 1;
            protected $dimensionId = '123-456';

            public function getContentId()
            {
                return 5;
            }
        };
    }

    public function testGetId(): void
    {
        $model = $this->getContentViewInstance();
        $this->assertSame(1, $model->getId());
    }

    public function testGetDimensionId(): void
    {
        $model = $this->getContentViewInstance();
        $this->assertSame('123-456', $model->getDimensionId());
    }

    public function testGetContentId(): void
    {
        $model = $this->getContentViewInstance();
        $this->assertSame(5, $model->getContentId());
    }
}
