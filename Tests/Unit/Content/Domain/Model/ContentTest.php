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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentDimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\TestCases\Content\ContentTestCaseTrait;

class ContentTest extends TestCase
{
    use ContentTestCaseTrait;

    protected function getInstance(): AbstractContent
    {
        return new class() extends AbstractContent {
        };
    }

    protected function getInstanceDimension(int $id): ContentDimensionInterface
    {
        $modelDimension = new class() extends AbstractContentDimension implements SeoInterface, ExcerptInterface, TemplateInterface {
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;

            public function setId(int $id): void
            {
                $this->id = $id;
            }
        };

        $modelDimension->setId($id);

        return $modelDimension;
    }
}
