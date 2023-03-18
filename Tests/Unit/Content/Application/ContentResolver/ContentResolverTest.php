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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentResolver;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

class ContentResolverTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

    protected function createContentResolverInstance(
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ContentMergerInterface $contentMerger
    ): ContentResolverInterface {
        return new ContentResolver(
            $dimensionContentRepository,
            $contentMerger
        );
    }

    public function testResolve(): void
    {
        $example = new Example();

        $dimensionContent1 = new ExampleDimensionContent($example);
        $dimensionContent1->setStage(DimensionContentInterface::STAGE_DRAFT);
        $dimensionContent1->setLocale(null);
        $dimensionContent2 = new ExampleDimensionContent($example);
        $dimensionContent2->setStage(DimensionContentInterface::STAGE_DRAFT);
        $dimensionContent2->setLocale(null);

        $attributes = [
            'locale' => 'de',
        ];

        $expectedAttributes = [
            'locale' => 'de',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ];

        $dimensionContentCollection = new DimensionContentCollection(
            [
                $dimensionContent1,
                $dimensionContent2,
            ],
            $expectedAttributes,
            ExampleDimensionContent::class
        );

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load($example, $attributes)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $mergedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentMerger->merge($dimensionContentCollection)
            ->willReturn($mergedDimensionContent->reveal())
            ->shouldBeCalled();

        $contentResolver = $this->createContentResolverInstance(
            $dimensionContentRepository->reveal(),
            $contentMerger->reveal()
        );

        $this->assertSame($mergedDimensionContent->reveal(), $contentResolver->resolve($example, $attributes));
    }

    public function testResolveNotFound(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $example = new Example();

        $attributes = [
            'locale' => 'de',
        ];

        $expectedAttributes = [
            'locale' => 'de',
            'stage' => DimensionContentInterface::STAGE_DRAFT,
        ];

        $dimensionContentCollection = new DimensionContentCollection(
            [],
            $expectedAttributes,
            ExampleDimensionContent::class
        );

        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $dimensionContentRepository->load($example, $attributes)->willReturn($dimensionContentCollection);

        $contentMerger = $this->prophesize(ContentMergerInterface::class);
        $contentMerger->merge($dimensionContentCollection)->willReturn(Argument::cetera())->shouldNotBeCalled();

        $contentResolver = $this->createContentResolverInstance(
            $dimensionContentRepository->reveal(),
            $contentMerger->reveal()
        );

        $contentResolver->resolve($example, $attributes);
    }
}
