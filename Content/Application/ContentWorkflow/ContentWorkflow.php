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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow;

use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface as SymfonyWorkflowInterface;

class ContentWorkflow
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var SymfonyWorkflowInterface
     */
    private $workflow;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->viewFactory = $viewFactory;
    }

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function transition(
        ContentInterface $content,
        array $dimensionAttributes,
        string $transitionName
    ): ContentViewInterface {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);
        $contentDimensionCollection = $this->contentDimensionRepository->load($content, $dimensionCollection);

        $localizedContentDimension = $contentDimensionCollection->getLocalizedContentDimension();

        if (!$localizedContentDimension) {
            throw new ContentNotFoundException($content, $dimensionAttributes);
        }

        if (!$localizedContentDimension instanceof WorkflowInterface) {
            throw new \RuntimeException(sprintf('Expected "%s" but "%s" given.', WorkflowInterface::class, \get_class($localizedContentDimension)));
        }

        // TODO get workflow from a pool for specific entity
        $workflow = $this->getWorkflow();

        $workflow->can($localizedContentDimension, $transitionName);

        return $this->viewFactory->create($contentDimensionCollection);
    }

    private function getWorkflow()
    {
        $definitionBuilder = new DefinitionBuilder();

        // Configures places
        $definition = $definitionBuilder->addPlaces([
            WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
            WorkflowInterface::WORKFLOW_PLACE_REVIEW,
            WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
            WorkflowInterface::WORKFLOW_PLACE_DRAFT,
            WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
        ])
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REQUEST_FOR_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REJECT,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                [
                    WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
                    WorkflowInterface::WORKFLOW_PLACE_REVIEW,
                    WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                    WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
                ]
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REQUEST_FOR_REVIEW_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REJECT_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT
            ))
            ->build();

        $singleState = true;
        $property = 'workflowPlace';
        $marking = new MethodMarkingStore($singleState, $property);

        return new Workflow($definition, $marking);
    }
}
