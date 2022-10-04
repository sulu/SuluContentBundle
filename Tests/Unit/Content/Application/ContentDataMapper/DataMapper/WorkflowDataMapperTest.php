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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;

class WorkflowDataMapperTest extends TestCase
{
    use \Prophecy\PhpUnit\ProphecyTrait;

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

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());

        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoWorkflow(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedDraft(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);
        $unlocalizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $unlocalizedDimensionContent->getWorkflowPlace()->willReturn(null);

        $unlocalizedDimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED)->shouldBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedDraftPlaceAlreadySet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);
        $unlocalizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $unlocalizedDimensionContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);
        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedLive(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);
        $unlocalizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);
        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::type(\DateTimeInterface::class))->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedLivePublishedNotSet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);
        $unlocalizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);
        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedDraft(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $localizedDimensionContent->getWorkflowPlace()->willReturn(null);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED)->shouldBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedDraftPlaceAlreadySet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_DRAFT);
        $localizedDimensionContent->getWorkflowPlace()->willReturn(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedLive(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [
            'published' => (new \DateTime())->format('c'),
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::type(\DateTimeInterface::class))->shouldBeCalled();

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedLivePublishedNotSet(): void
    {
        $workflowMapper = $this->createWorkflowDataMapperInstance();

        $data = [];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(WorkflowInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(WorkflowInterface::class);
        $localizedDimensionContent->getStage()->willReturn(DimensionContentInterface::STAGE_LIVE);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $unlocalizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $unlocalizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->setWorkflowPlace(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setWorkflowPublished(Argument::cetera())->shouldNotBeCalled();

        $this->expectException(\RuntimeException::class);

        $workflowMapper->map($data, $dimensionContentCollection->reveal());
    }
}
