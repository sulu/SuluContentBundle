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
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Enhancer\WorkflowNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowNormalizeEnhancerTest extends TestCase
{
    protected function createWorkflowNormalizeEnhancerInstance(): WorkflowNormalizeEnhancer
    {
        return new WorkflowNormalizeEnhancer();
    }

    public function testIgnoredAttributesNoneContentProjection(): void
    {
        $enhancer = $this->createWorkflowNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $this->assertSame(
            [],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testIgnoredAttributes(): void
    {
        $enhancer = $this->createWorkflowNormalizeEnhancerInstance();
        $object = $this->prophesize(WorkflowInterface::class);

        $this->assertSame(
            [],
            $enhancer->getIgnoredAttributes($object->reveal())
        );
    }

    public function testEnhanceNotSupported(): void
    {
        $enhancer = $this->createWorkflowNormalizeEnhancerInstance();
        $object = $this->prophesize(ContentProjectionInterface::class);

        $data = [
            'workflowPublished' => '12345',
            'publishedState' => '123',
            'published' => '456',
        ];

        $this->assertSame(
            $data,
            $enhancer->enhance($object->reveal(), $data)
        );
    }

    public function testEnhancePublished(): void
    {
        $enhancer = $this->createWorkflowNormalizeEnhancerInstance();
        $object = $this->prophesize(WorkflowInterface::class);

        $data = [
            'workflowPlace' => 'published',
            'workflowPublished' => '2019-01-01',
        ];

        $expectedResult = [
            'workflowPlace' => 'published',
            'publishedState' => true,
            'published' => '2019-01-01',
        ];

        $this->assertSame(
            $expectedResult,
            $enhancer->enhance($object->reveal(), $data)
        );
    }

    public function testEnhanceNotPublished(): void
    {
        $enhancer = $this->createWorkflowNormalizeEnhancerInstance();
        $object = $this->prophesize(WorkflowInterface::class);

        $data = [
            'workflowPlace' => 'unpublished',
            'workflowPublished' => null,
        ];

        $expectedResult = [
            'workflowPlace' => 'unpublished',
            'publishedState' => false,
            'published' => null,
        ];

        $this->assertSame(
            $expectedResult,
            $enhancer->enhance($object->reveal(), $data)
        );
    }
}
