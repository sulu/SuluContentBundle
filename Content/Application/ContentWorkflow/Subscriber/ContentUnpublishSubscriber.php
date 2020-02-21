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
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ContentUnpublishSubscriber implements EventSubscriberInterface
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var DimensionContentRepositoryInterface
     */
    private $dimensionContentRepository;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->entityManager = $entityManager;
    }

    public function onUnpublish(TransitionEvent $transitionEvent): void
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

        $liveDimensionAttributes = array_merge($dimensionAttributes, ['stage' => DimensionInterface::STAGE_LIVE]);

        // find all dimensions that contain all attributes, but no dimension that is less specific
        $dimensionCollection = $this->dimensionRepository->findByAttributes($liveDimensionAttributes);
        if (0 === \count($dimensionCollection)) {
            throw new ContentNotFoundException($contentRichEntity, $liveDimensionAttributes);
        }

        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $dimensionCollection);
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
