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

namespace Sulu\Bundle\ContentBundle\Content\Application\MessageHandler;

use Sulu\Bundle\ContentBundle\Content\Application\Message\CopyContentDimensionMessage;
use Sulu\Bundle\ContentBundle\Content\Application\Message\WorkflowTransitionContentMessage;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class WorkflowTransitionContentMessageHandler
{
    use HandleTrait;

    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var MessageBusInterface
     */
    private $suluContentMessageBus;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        MessageBusInterface $suluContentMessageBus
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->messageBus = $suluContentMessageBus;
    }

    public function __invoke(WorkflowTransitionContentMessage $message): array
    {
        $content = $message->getContent();
        $fromDimensionAttributes = $message->getDimensionAttributes();
        $dummyDimension = $this->dimensionRepository->create('dummy', $fromDimensionAttributes);
        $fromWorkflowStage = $dummyDimension->getWorkflowStage();

        $toDimensionAttributes = $fromDimensionAttributes;
        $toWorkflowStage = $message->getToWorkflowStage();
        $toDimensionAttributes['workflowStage'] = $toWorkflowStage;

        // TODO use workflow component?
        if (DimensionInterface::WORKFLOW_STAGE_DRAFT !== $fromWorkflowStage) {
            throw new \LogicException(sprintf('Can not transform from "%s" to "%s".', $fromWorkflowStage, $toWorkflowStage));
        }

        if (DimensionInterface::WORKFLOW_STAGE_LIVE !== $toWorkflowStage) {
            throw new \LogicException(sprintf('Can not transform from "%s" to "%s".', $fromWorkflowStage, $toWorkflowStage));
        }

        return $this->handle(
            new CopyContentDimensionMessage($content, $fromDimensionAttributes, $toDimensionAttributes)
        );
    }
}
