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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;

/**
 * Trait to test your implementation of the ContentDimensionInterface.
 */
trait ContentDimensionTestCaseTrait
{
    abstract protected function getContentDimensionInstance(): ContentDimensionInterface;

    public function testGetId(): void
    {
        $model = $this->getContentDimensionInstance();
        $this->assertSame(1, $model->getId());
    }

    public function testGetDimensionId(): void
    {
        $model = $this->getContentDimensionInstance();
        $this->assertSame('123-456', $model->getDimensionId());
    }
}
