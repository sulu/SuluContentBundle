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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;

class DimensionContentTraitTest extends TestCase
{
    protected function getDimensionContentInstance(): DimensionContentInterface
    {
        return new class() implements DimensionContentInterface {
            use DimensionContentTrait;

            public static function getResourceKey(): string
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }

            public function getResource(): ContentRichEntityInterface
            {
                throw new \RuntimeException('Should not be called while executing tests.');
            }
        };
    }

    public function testGetSetLocale(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertNull($model->getLocale());
        $model->setLocale('de');
        $this->assertSame('de', $model->getLocale());
    }

    public function testGetSetStage(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertSame('draft', $model->getStage());
        $model->setStage('live');
        $this->assertSame('live', $model->getStage());
    }

    public function testGetDefaultAttributes(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertSame([
            'locale' => null,
            'stage' => 'draft',
        ], $model::getDefaultDimensionAttributes());
    }

    public function testGetSetIsMerged(): void
    {
        $model = $this->getDimensionContentInstance();
        $this->assertFalse($model->isMerged());

        $model->markAsMerged();
        $this->assertTrue($model->isMerged());
    }
}
