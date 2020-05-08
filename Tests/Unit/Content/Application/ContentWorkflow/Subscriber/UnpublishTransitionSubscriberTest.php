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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentWorkflow\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber\UnpublishTransitionSubscriber;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

class UnpublishTransitionSubscriberTest extends TestCase
{
    public function createContentUnpublishSubscriberInstance(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        EntityManagerInterface $entityManager
    ): UnpublishTransitionSubscriber {
        return new UnpublishTransitionSubscriber($dimensionRepository, $dimensionContentRepository, $entityManager);
    }

    public function testGetSubscribedEvents(): void
    {
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $this->assertSame([
            'workflow.content_workflow.transition.unpublish' => 'onUnpublish',
        ], $contentUnpublishSubscriber::getSubscribedEvents());
    }

    public function testOnUnpublishNoDimensionContentInterface(): void
    {
        $dimensionContent = $this->prophesize(WorkflowInterface::class);
        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $entityManager->remove(Argument::cetera())->shouldNotBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }

    public function testOnUnpublishNoDimensionAttributes(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transition context must contain "dimensionAttributes".');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $entityManager->remove(Argument::cetera())->shouldNotBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }

    public function testOnUnpublishNoContentRichEntity(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Transition context must contain "contentRichEntity".');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
        ]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $entityManager->remove(Argument::cetera())->shouldNotBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }

    public function testOnUnpublishEmptyDimensionCollection(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $dimensionCollection = $this->prophesize(DimensionCollectionInterface::class);
        $dimensionCollection->count()->willReturn(0)->shouldBeCalled();

        $dimensionRepository->findByAttributes(['locale' => 'en', 'stage' => 'live'])
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $entityManager->remove(Argument::cetera())->shouldNotBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }

    public function testOnUnpublishNoLocalizedDimensionContent(): void
    {
        $this->expectException(ContentNotFoundException::class);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $dimensionCollection = $this->prophesize(DimensionCollectionInterface::class);
        $dimensionCollection->count()->willReturn(1)->shouldBeCalled();

        $dimensionRepository->findByAttributes(['locale' => 'en', 'stage' => 'live'])
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getLocalizedDimensionContent()
            ->willReturn(null)
            ->shouldBeCalled();

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $entityManager->remove(Argument::cetera())->shouldNotBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }

    public function testOnUnpublish(): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(WorkflowInterface::class);
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => 'draft'];

        $dimensionContent->setWorkflowPublished(null)->shouldBeCalled();

        $event = new TransitionEvent(
            $dimensionContent->reveal(),
            new Marking()
        );
        $event->setContext([
            'dimensionAttributes' => $dimensionAttributes,
            'contentRichEntity' => $contentRichEntity->reveal(),
        ]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionContentRepository = $this->prophesize(DimensionContentRepositoryInterface::class);
        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentUnpublishSubscriber = $this->createContentUnpublishSubscriberInstance(
            $dimensionRepository->reveal(),
            $dimensionContentRepository->reveal(),
            $entityManager->reveal()
        );

        $dimensionCollection = $this->prophesize(DimensionCollectionInterface::class);
        $dimensionCollection->count()->willReturn(1)->shouldBeCalled();

        $dimensionRepository->findByAttributes(['locale' => 'en', 'stage' => 'live'])
            ->willReturn($dimensionCollection)
            ->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContentCollection->getLocalizedDimensionContent()
            ->willReturn($localizedDimensionContent)
            ->shouldBeCalled();

        $dimensionContentRepository->load($contentRichEntity->reveal(), $dimensionCollection)
            ->willReturn($dimensionContentCollection)
            ->shouldBeCalled();

        $entityManager->remove($localizedDimensionContent)->shouldBeCalled();

        $contentUnpublishSubscriber->onUnpublish($event);
    }
}
