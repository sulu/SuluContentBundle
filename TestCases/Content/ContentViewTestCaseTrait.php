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

namespace Sulu\Bundle\ContentBundle\TestCases\Content;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

/**
 * Trait to test your implementation of the ContentViewInterface.
 */
trait ContentViewTestCaseTrait
{
    abstract protected function getContentViewInstance(): ContentViewInterface;

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
