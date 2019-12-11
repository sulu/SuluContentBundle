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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;

class ContentPublishSubscriber implements EventSubscriberInterface
{
    /**
     * @var ContentCopierInterface
     */
    private $contentCopier;

    public function __construct(ContentCopierInterface $contentCopier)
    {
        $this->contentCopier = $contentCopier;
    }

    public function onPublish(TransitionEvent $transitionEvent): void
    {
        $contentDimension = $transitionEvent->getSubject();

        if (!$contentDimension instanceof ContentDimensionInterface) {
            return;
        }

        $context = $transitionEvent->getContext();

        $contentDimensionCollection = $context['contentDimensionCollection'] ?? null;
        $dimensionAttributes = $context['dimensionAttributes'] ?? null;
        $content = $context['contentRichEntity'] ?? null;

        if (!$dimensionAttributes) {
            throw new \RuntimeException('No "dimensionAttributes" given.');
        }

        if (!$contentDimensionCollection instanceof ContentDimensionCollectionInterface) {
            throw new \RuntimeException('No "contentDimensionCollection" given.');
        }

        if (!$content instanceof ContentInterface) {
            throw new \RuntimeException('No "contentRichEntity" given.');
        }

        $dimensionAttributes['stage'] = DimensionInterface::STAGE_LIVE;

        $this->contentCopier->copyFromContentDimensionCollection(
            $contentDimensionCollection,
            $content,
            $dimensionAttributes
        );
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'workflow.content_workflow.transition.publish' => 'onPublish',
        ];
    }
}
