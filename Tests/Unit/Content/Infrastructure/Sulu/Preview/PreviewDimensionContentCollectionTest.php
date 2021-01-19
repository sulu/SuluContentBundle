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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Structure;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\PreviewDimensionContentCollection;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

class PreviewDimensionContentCollectionTest extends TestCase
{
    protected function createPreviewDimensionContentCollection(
        ?DimensionContentInterface $previewDimensionContent = null,
        string $locale = 'en'
    ): PreviewDimensionContentCollection {
        return new PreviewDimensionContentCollection(
            $previewDimensionContent ?: $this->prophesize(DimensionContentInterface::class)->reveal(),
            $locale
        );
    }

    public function testGetDimensionContentClass(): void
    {
        $example = $this->prophesize(Example::class);
        $dimensionContent = new ExampleDimensionContent($example->reveal());

        $previewDimensionContentCollection = $this->createPreviewDimensionContentCollection($dimensionContent);

        $this->assertSame(
            ExampleDimensionContent::class,
            $previewDimensionContentCollection->getDimensionContentClass()
        );
    }

    public function testGetDimensionContent(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $previewDimensionContentCollection = $this->createPreviewDimensionContentCollection($dimensionContent->reveal());

        $this->assertSame(
            $dimensionContent->reveal(),
            $previewDimensionContentCollection->getDimensionContent([])
        );
        $this->assertSame(
            $dimensionContent->reveal(),
            $previewDimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
        );
        $this->assertSame(
            $dimensionContent->reveal(),
            $previewDimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'en'])
        );
    }

    public function testGetDimensionAttributes(): void
    {
        $example = $this->prophesize(Example::class);
        $dimensionContent = new ExampleDimensionContent($example->reveal());

        $previewDimensionContentCollection = $this->createPreviewDimensionContentCollection(
            $dimensionContent,
            'es'
        );

        $this->assertSame(
            ['locale' => 'es', 'stage' => 'draft'],
            $previewDimensionContentCollection->getDimensionAttributes()
        );
    }

    public function testGetIterator(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $previewDimensionContentCollection = $this->createPreviewDimensionContentCollection($dimensionContent->reveal());

        $this->assertSame(
            [$dimensionContent->reveal()],
            iterator_to_array($previewDimensionContentCollection)
        );
    }

    public function testGetCount(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $previewDimensionContentCollection = $this->createPreviewDimensionContentCollection($dimensionContent->reveal());

        $this->assertCount(1, $previewDimensionContentCollection);
    }
}
