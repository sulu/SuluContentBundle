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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;

class WorkflowTraitTest extends TestCase
{
    use WorkflowTrait;

    protected function getWorkflowInstance(): WorkflowInterface
    {
        return new class() implements WorkflowInterface {
            use WorkflowTrait;
        };
    }

    public function testGetWorkflowPlace(): void
    {
        $workflow = $this->getWorkflowInstance();
        $this->assertSame('unpublished', $workflow->getWorkflowPlace());
    }

    public function testSetWorkflowPlaceReview(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_REVIEW);
        $this->assertSame('review', $workflow->getWorkflowPlace());
    }

    public function testSetWorkflowPlaceUnpublished(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED);
        $this->assertSame('unpublished', $workflow->getWorkflowPlace());
    }

    public function testSetWorkflowPlaceDraft(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_DRAFT);
        $this->assertSame('draft', $workflow->getWorkflowPlace());
    }

    public function testSetWorkflowPlacePublished(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->assertSame('published', $workflow->getWorkflowPlace());
        $published = $workflow->getWorkflowPublished();
        $this->assertInstanceOf(\DateTimeImmutable::class, $published);
        $this->assertSame(date('Y-m-d H:i:s'), $published->format('Y-m-d H:i:s'));
    }

    public function testSetWorkflowPlacePublishedExistingPublishedDate(): void
    {
        $date = new \DateTimeImmutable('2019-01-01');

        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPublished($date);

        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_PUBLISHED);
        $this->assertSame('published', $workflow->getWorkflowPlace());
        $this->assertSame($date, $workflow->getWorkflowPublished());
    }

    public function testSetWorkflowPlaceReviewDraft(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPlace(WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT);
        $this->assertSame('review_draft', $workflow->getWorkflowPlace());
    }

    public function testGetWorkflowPublished(): void
    {
        $workflow = $this->getWorkflowInstance();
        $this->assertNull($workflow->getWorkflowPublished());
    }

    public function testSetWorkflowPublished(): void
    {
        $workflow = $this->getWorkflowInstance();
        $dateTime = new \DateTimeImmutable();
        $workflow->setWorkflowPublished($dateTime);
        $this->assertSame($dateTime, $workflow->getWorkflowPublished());
    }

    public function testSetWorkflowPublishedNull(): void
    {
        $workflow = $this->getWorkflowInstance();
        $workflow->setWorkflowPublished(null);
        $this->assertNull($workflow->getWorkflowPublished());
    }

    public function testGetWorkflowName(): void
    {
        $workflow = $this->getWorkflowInstance();
        $this->assertSame('content_workflow', $workflow->getWorkflowName());
    }
}
