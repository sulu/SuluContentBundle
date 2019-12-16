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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentRichEntity;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractDimensionContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;

class ContentRichEntityTest extends TestCase
{
    protected function getInstance(): AbstractContentRichEntity
    {
        return new class() extends AbstractContentRichEntity {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
            {
                throw new \RuntimeException();
            }

            public function getId()
            {
                return null;
            }
        };
    }

    protected function getInstanceDimension(int $id): DimensionContentInterface
    {
        $modelDimension = new class() extends AbstractDimensionContent implements SeoInterface, ExcerptInterface, TemplateInterface {
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;

            public function setId(int $id): void
            {
                $this->id = $id;
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

            public function getTemplateType(): string
            {
                return 'example';
            }
        };

        $modelDimension->setId($id);

        return $modelDimension;
    }

    protected function getInstanceView(int $id): ContentViewInterface
    {
        $modelDimension = new class() extends AbstractContentView implements SeoInterface, ExcerptInterface, TemplateInterface {
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;

            public function setId(int $id): void
            {
                $this->id = $id;
            }

            public function getContentId()
            {
                return 5;
            }

            public function getTemplateType(): string
            {
                return 'example';
            }
        };

        $modelDimension->setId($id);

        return $modelDimension;
    }

    public function testGetAddRemoveDimension(): void
    {
        $model = $this->getInstance();

        $this->assertEmpty($model->getDimensionContents());

        $modelDimension1 = $this->getInstanceDimension(1);
        $modelDimension2 = $this->getInstanceDimension(2);

        $model->addDimensionContent($modelDimension1);
        $model->addDimensionContent($modelDimension2);

        $this->assertSame([
            $modelDimension1,
            $modelDimension2,
        ], iterator_to_array($model->getDimensionContents()));

        $model->removeDimensionContent($modelDimension2);

        $this->assertSame([
            $modelDimension1,
        ], iterator_to_array($model->getDimensionContents()));
    }
}
