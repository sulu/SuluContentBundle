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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\TestCases\Content\ContentDimensionTestCaseTrait;

class ContentDimensionTest extends TestCase
{
    use ContentDimensionTestCaseTrait;

    protected function getContentDimensionInstance(): ContentDimensionInterface
    {
        return new class() extends AbstractContentDimension {
            protected $id = 1;
            protected $dimensionId = '123-456';
        };
    }

    protected function getFullContentDimensionInstance(): ContentDimensionInterface
    {
        return new class() extends AbstractContentDimension implements ExcerptInterface, SeoInterface, TemplateInterface {
            protected $id = 1;
            protected $dimensionId = '123-456';

            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;
        };
    }
}
