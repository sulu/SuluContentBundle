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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentNormalizer\Normalizer;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Normalizer\ContentProjectionNormalizer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;

class ContentProjectionNormalizerTest extends TestCase
{
    protected function createContentProjectionNormalizerInstance(): ContentProjectionNormalizer
    {
        return new ContentProjectionNormalizer();
    }

    public function testIgnoredAttributes(): void
    {
        $normalizer = $this->createContentProjectionNormalizerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $this->assertSame(
            [
                'id',
                'dimension',
            ],
            $normalizer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testIgnoredAttributesOtherType(): void
    {
        $normalizer = $this->createContentProjectionNormalizerInstance();
        $object = $this->prophesize(\stdClass::class);

        $this->assertSame(
            [],
            $normalizer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testEnhance(): void
    {
        $normalizer = $this->createContentProjectionNormalizerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $data = [
            'key' => 'value',
            'contentId' => 'content-rich-entity-id',
        ];

        $expectedResult = [
            'key' => 'value',
            'id' => 'content-rich-entity-id',
        ];

        $this->assertSame(
            $expectedResult,
            $normalizer->enhance($object->reveal(), $data)
        );
    }

    public function testEnhanceNotSupported(): void
    {
        $normalizer = $this->createContentProjectionNormalizerInstance();
        $object = $this->prophesize(\stdClass::class);

        $data = [
            'key' => 'value',
            'contentId' => 'content-rich-entity-id',
        ];

        $this->assertSame(
            $data,
            $normalizer->enhance($object->reveal(), $data)
        );
    }
}
