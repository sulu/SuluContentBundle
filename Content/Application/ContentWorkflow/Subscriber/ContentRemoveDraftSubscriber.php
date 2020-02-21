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

use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ContentRemoveDraftSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContentCopierInterface
     */
    private $contentCopier;

    public function __construct(ContentCopierInterface $contentCopier)
    {
        $this->contentCopier = $contentCopier;
    }

    public function onRemoveDraft(TransitionEvent $transitionEvent): void
    {
        if (!$transitionEvent->getSubject() instanceof DimensionContentInterface) {
            return;
        }

        $context = $transitionEvent->getContext();

        $dimensionAttributes = $context['dimensionAttributes'] ?? null;
        if (!$dimensionAttributes) {
            throw new \RuntimeException('Transition context must contain "dimensionAttributes".');
        }

        $contentRichEntity = $context['contentRichEntity'] ?? null;
        if (!$contentRichEntity instanceof ContentRichEntityInterface) {
            throw new \RuntimeException('Transition context must contain "contentRichEntity".');
        }

        $draftDimensionAttributes = array_merge($dimensionAttributes, ['stage' => DimensionInterface::STAGE_DRAFT]);
        $liveDimensionAttributes = array_merge($dimensionAttributes, ['stage' => DimensionInterface::STAGE_LIVE]);

        $this->contentCopier->copy(
            $contentRichEntity,
            $liveDimensionAttributes,
            $contentRichEntity,
            $draftDimensionAttributes
        );
    }

    public static function getSubscribedEvents(): array
    {
        $eventName = 'workflow.content_workflow.transition.' . WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT;

        return [
            $eventName => 'onRemoveDraft',
        ];
    }
}
