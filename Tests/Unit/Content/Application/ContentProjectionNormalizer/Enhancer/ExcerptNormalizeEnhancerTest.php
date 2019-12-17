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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentProjectionNormalizer\Enhancer;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Enhancer\ExcerptNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptNormalizeEnhancerTest extends TestCase
{
    protected function createExcerptNormalizeEnhancerInstance(): ExcerptNormalizeEnhancer
    {
        return new ExcerptNormalizeEnhancer();
    }

    public function testIgnoredAttributesNoneContentProjection(): void
    {
        $enhancer = $this->createExcerptNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $this->assertSame(
            [],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testIgnoredAttributes(): void
    {
        $enhancer = $this->createExcerptNormalizeEnhancerInstance();
        $object = $this->prophesize(ExcerptInterface::class);

        $this->assertSame(
            [
                'excerptTags',
                'excerptCategories',
            ],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testEnhanceNotSupported(): void
    {
        $enhancer = $this->createExcerptNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $data = [
            'excerptTagNames' => '12345',
            'excerptCategoryIds' => '123',
        ];

        $this->assertSame(
            $data,
            $enhancer->enhance($object->reveal(), $data)
        );
    }

    public function testEnhance(): void
    {
        $enhancer = $this->createExcerptNormalizeEnhancerInstance();
        $object = $this->prophesize(ExcerptInterface::class);

        $data = [
            'excerptTagNames' => ['Tag 1', 'Tag 2'],
            'excerptCategoryIds' => [3, 4],
        ];

        $expectedResult = [
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [3, 4],
        ];

        $this->assertSame(
            $expectedResult,
            $enhancer->enhance($object->reveal(), $data)
        );
    }
}
