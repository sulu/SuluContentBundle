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
use Sulu\Bundle\ContentBundle\TestCases\Content\ContentDimensionTestCaseTrait;

class ContentDimensionTest extends TestCase
{
    use ContentDimensionTestCaseTrait;

    protected function getContentDimensionInstance(): ContentDimensionInterface
    {
        return new class() extends AbstractContentDimension {
            protected $id = 1;
            protected $dimensionId = '123-456';

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
}
