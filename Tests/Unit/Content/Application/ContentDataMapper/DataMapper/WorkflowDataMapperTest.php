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

    public function testMapUnlocalizedDraft(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $dimensionContent->getWorkflowPlace()->willReturn(null);

        $dimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED)->shouldBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapUnlocalizedDraftPlaceAlreadySet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $dimensionContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);
        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapUnlocalizedLive(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);
        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::type(\DateTimeInterface::class))->shouldBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapUnlocalizedLivePublishedNotSet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $dimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);
        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapLocalizedDraft(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $localizedDimensionContent->getWorkflowPlace()->willReturn(null);

        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED)->shouldBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedDraftPlaceAlreadySet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $localizedDimensionContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);

        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedLive(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);

        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::type(\DateTimeInterface::class))->shouldBeCalled();

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedLivePublishedNotSet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);

        $dimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $dimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }
}
