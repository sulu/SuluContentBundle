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

    abstract protected function getFullContentDimensionInstance(): ContentDimensionInterface;

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

    public function testDimensionToArray(): void
    {
        $model = $this->getContentDimensionInstance();
        $this->assertSame([], $this->dimensionToArray($model));
    }

    public function testDimensionToArrayFull(): void
    {
        $model = $this->getFullContentDimensionInstance();
        $this->assertSame([], $this->dimensionToArray($model));
    }

    /**
     * Overwrite this function to unset custom data.
     *
     * @return mixed[]
     */
    protected function dimensionToArray(ContentDimensionInterface $model): array
    {
        return $model->dimensionToArray();
    }
}
