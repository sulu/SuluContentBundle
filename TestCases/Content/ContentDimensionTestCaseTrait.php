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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

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

    public function testGetData(): void
    {
        $model = $this->getContentDimensionInstance();

        $this->assertSame([], array_keys($this->getData($model)));
    }

    public function testGetDataFull(): void
    {
        /** @var SeoInterface|ExcerptInterface|TemplateInterface|ContentDimensionInterface $model */
        $model = $this->getFullContentDimensionInstance();

        $this->assertInstanceOf(ContentDimensionInterface::class, $model);
        $this->assertInstanceOf(SeoInterface::class, $model);
        $this->assertInstanceOf(ExcerptInterface::class, $model);
        $this->assertInstanceOf(TemplateInterface::class, $model);

        $model->setTemplate('template');

        $this->assertSame([
            'seo',
            'excerpt',
            'template',
        ], array_keys($this->getData($model)));
    }

    /**
     * Overwrite this function to unset custom data.
     *
     * @return mixed[]
     */
    protected function getData(ContentDimensionInterface $model): array
    {
        return $model->getData();
    }
}
