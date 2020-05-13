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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDataMapper\DataMapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\WorkflowDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowDataMapperTest extends TestCase
{
    protected function createWorkflowDataMapperInstance(): WorkflowDataMapper
    {
        return new WorkflowDataMapper();
    }

    public function testMapNoWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());

        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapUnlocalizedWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapLocalizedWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);
        $localizedDimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapUnlocalizedDraftWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapLocalizedDraftWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $localizedDimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapPublishedNotExists(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapPublishedNull(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_LIVE);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $dimensionContent->setWorkflowPublished(Argument::any())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }
}
