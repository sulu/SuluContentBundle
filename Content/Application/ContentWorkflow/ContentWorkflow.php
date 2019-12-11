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

use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentInvalidTransitionException;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotExistTransitionException;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\DefinitionBuilder;
use Symfony\Component\Workflow\Exception\NotEnabledTransitionException;
use Symfony\Component\Workflow\Exception\UndefinedTransitionException;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\Workflow\WorkflowInterface as SymfonyWorkflowInterface;

class ContentWorkflow implements ContentWorkflowInterface
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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Registry
     */
    private $workflowRegistry;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        ViewFactoryInterface $viewFactory,
        ?Registry $workflowRegistry = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->viewFactory = $viewFactory;
        $this->eventDispatcher = $eventDispatcher ?: new EventDispatcher();
        // TODO get workflow from outside
        $this->workflowRegistry = $workflowRegistry ?: new Registry();
        $this->workflowRegistry->addWorkflow(
            $this->getWorkflow(),
            new InstanceOfSupportStrategy(WorkflowInterface::class)
        );
    }

    public function apply(
        ContentInterface $content,
        array $dimensionAttributes,
        string $transitionName
    ): ContentViewInterface {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);

        if (0 === \count($dimensionCollection)) {
            throw new ContentNotFoundException($content, $dimensionAttributes);
        }

        $contentDimensionCollection = $this->contentDimensionRepository->load($content, $dimensionCollection);

        $localizedContentDimension = $contentDimensionCollection->getLocalizedContentDimension();

        if (!$localizedContentDimension) {
            throw new ContentNotFoundException($content, $dimensionAttributes);
        }

        if (!$localizedContentDimension instanceof WorkflowInterface) {
            throw new \RuntimeException(sprintf('Expected "%s" but "%s" given.', WorkflowInterface::class, \get_class($localizedContentDimension)));
        }

        $workflow = $this->workflowRegistry->get(
            $localizedContentDimension,
            $localizedContentDimension->getWorkflowName()
        );

        try {
            $workflow->apply($localizedContentDimension, $transitionName, [
                'contentRichEntity' => $content,
                'contentDimensionCollection' => $contentDimensionCollection,
                'dimensionAttributes' => $dimensionAttributes,
            ]);
        } catch (UndefinedTransitionException $e) {
            throw new ContentNotExistTransitionException($e->getMessage(), $e->getCode(), $e);
        } catch (NotEnabledTransitionException $e) {
            throw new ContentInvalidTransitionException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->viewFactory->create($contentDimensionCollection);
    }

    private function getWorkflow(): SymfonyWorkflowInterface
    {
        $definitionBuilder = new DefinitionBuilder();

        //                                                           unpublish
        //                   +--------------------------------------------------------------------------------------------+
        //                   |                                                                                            |
        //                   |                              publish                                                       |
        //                   |     +--------------------------------------------------------+                             |
        //                   |     |                                                        |                             |
        //                   |     |                       unpublish                        |           publish           |
        //                   |     |     +----------------------------------------------+   |   +---------------------+   |
        //                   V     |     V                                              |   V   V                     |   |
        // +-----+          +-------------+  request for review  +--------+           +------------+  remove draft  +-------+  request draft for review   +---------------+
        // |     |  create  |             |--------------------->|        |  publish  |            |<---------------|       |---------------------------->|               |
        // | New |--------->| Unpublished |                      | Review |---------->| Published  |                | draft |                             | Review draft  |
        // |     |          |             |<---------------------|        |           |            |--------------->|       |<----------------------------|               |
        // +-----+          +-------------+       reject         +--------+           +------------+  create draft  +-------+        reject draft         +---------------+
        //                                                                                  A                                                                     |
        //                                                                                  |                             publish                                 |
        //                                                                                  +---------------------------------------------------------------------+

        // Configures places
        $definition = $definitionBuilder
            ->addPlaces([
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
            ])
            ->setInitialPlaces([
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
            ])
            // Transfer a unpublished to review
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REQUEST_FOR_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW
            ))
            // Reject a review back to unpublish
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REJECT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED
            ))
            // Transfer to publish
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            // Unpublish published
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED
            ))
            // Unpublish draft
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_UNPUBLISH,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_UNPUBLISHED
            ))
            // Create a draft out of a published
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_CREATE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT
            ))
            // Remove a draft
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REMOVE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_PUBLISHED
            ))
            // Request a review for a draft
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REQUEST_FOR_REVIEW_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT
            ))
            // Reject a review of a draft
            ->addTransition(new Transition(
                WorkflowInterface::WORKFLOW_TRANSITION_REJECT_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_REVIEW_DRAFT,
                WorkflowInterface::WORKFLOW_PLACE_DRAFT
            ))
            ->build();

        $singleState = true;
        $property = 'workflowPlace';
        $marking = new MethodMarkingStore($singleState, $property);

        return new Workflow(
            $definition,
            $marking,
            $this->eventDispatcher,
            WorkflowInterface::WORKFLOW_DEFAULT_NAME
        );
    }
}
