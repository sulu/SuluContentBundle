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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentNormalizer\Enhancer;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Enhancer\TemplateNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateNormalizeEnhancerTest extends TestCase
{
    protected function createTemplateNormalizeEnhancerInstance(): TemplateNormalizeEnhancer
    {
        return new TemplateNormalizeEnhancer();
    }

    public function testIgnoredAttributesNoneContentProjection(): void
    {
        $enhancer = $this->createTemplateNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $this->assertSame(
            [],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testIgnoredAttributes(): void
    {
        $enhancer = $this->createTemplateNormalizeEnhancerInstance();
        $object = $this->prophesize(TemplateInterface::class);

        $this->assertSame(
            [
                'templateType',
            ],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testEnhanceNotSupported(): void
    {
        $enhancer = $this->createTemplateNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $data = [
            'templateData' => [
                'some' => 'data',
            ],
            'templateKey' => 'some-key',
        ];

        $this->assertSame(
            $data,
            $enhancer->enhance($object->reveal(), $data)
        );
    }

    public function testEnhance(): void
    {
        $enhancer = $this->createTemplateNormalizeEnhancerInstance();
        $object = $this->prophesize(TemplateInterface::class);

        $data = [
            'templateData' => [
                'existingKey' => 'template-data-value',
                'newKey' => 'template-data-value',
            ],
            'templateKey' => 'some-key',
            'existingKey' => 'existing-value',
        ];

        $expectedResult = [
            'existingKey' => 'existing-value',
            'newKey' => 'template-data-value',
            'template' => 'some-key',
        ];

        $this->assertSame(
            $expectedResult,
            $enhancer->enhance($object->reveal(), $data)
        );
    }
}
