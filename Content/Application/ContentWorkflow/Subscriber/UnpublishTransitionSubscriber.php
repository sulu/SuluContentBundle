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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\Subscriber;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class UnpublishTransitionSubscriber implements EventSubscriberInterface
{
    /**
     * @var DimensionContentRepositoryInterface
     */
    private $dimensionContentRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        DimensionContentRepositoryInterface $dimensionContentRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->entityManager = $entityManager;
    }

    public function onUnpublish(TransitionEvent $transitionEvent): void
    {
        $dimensionContent = $transitionEvent->getSubject();

        if (!$dimensionContent instanceof DimensionContentInterface) {
            return;
        }

        if ($dimensionContent instanceof WorkflowInterface) {
            $dimensionContent->setWorkflowPublished(null);
        }

        $context = $transitionEvent->getContext();

        $dimensionAttributes = $context[ContentWorkflowInterface::DIMENSION_ATTRIBUTES_CONTEXT_KEY] ?? null;
        if (!$dimensionAttributes) {
            throw new \RuntimeException('Transition context must contain "dimensionAttributes".');
        }

        $contentRichEntity = $context[ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY] ?? null;
        if (!$contentRichEntity instanceof ContentRichEntityInterface) {
            throw new \RuntimeException('Transition context must contain "contentRichEntity".');
        }

        $liveDimensionAttributes = array_merge($dimensionAttributes, ['stage' => DimensionContentInterface::STAGE_LIVE]);

        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $liveDimensionAttributes);
        $localizedDimensionContent = $dimensionContentCollection->getLocalizedDimensionContent();
        if (!$localizedDimensionContent) {
            throw new ContentNotFoundException($contentRichEntity, $liveDimensionAttributes);
        }

        $this->entityManager->remove($localizedDimensionContent);
    }

    public static function getSubscribedEvents(): array
    {
        $eventName = 'workflow.content_workflow.transition.' . WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH;

        return [
            $eventName => 'onUnpublish',
        ];
    }
}
