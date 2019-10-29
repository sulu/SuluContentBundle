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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

/**
 * Trait to test your implementation of the ContentInterface.
 */
trait ContentTestCaseTrait
{
    abstract protected function getInstance(): ContentInterface;

    abstract protected function getInstanceDimension(int $id): ContentDimensionInterface;

    public function testGetAddRemoveDimension(): void
    {
        $model = $this->getInstance();

        $this->assertEmpty($model->getDimensions());

        $modelDimension1 = $this->getInstanceDimension(1);
        $modelDimension2 = $this->getInstanceDimension(2);

        $model->addDimension($modelDimension1);
        $model->addDimension($modelDimension2);

        $this->assertSame([
            $modelDimension1,
            $modelDimension2,
        ], $model->getDimensions());

        $model->removeDimension($modelDimension2);

        $this->assertSame([
            $modelDimension1,
        ], $model->getDimensions());
    }
}
