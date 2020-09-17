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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContentPublishTaskHandler implements AutomationTaskHandlerInterface
{
    /**
     * @var ContentManagerInterface
     */
    private $contentManager;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(ContentManagerInterface $contentManager, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $entityClass): bool
    {
        return is_subclass_of($entityClass, ContentRichEntityInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans('sulu_content.task_handler.publish', [], 'admin'));
    }

    /**
     * {@inheritdoc}
     */
    public function handle($workload)
    {
        if (!\is_array($workload)) {
            return;
        }

        /** @var class-string<ContentRichEntityInterface> $class */
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);

        /** @var ContentRichEntityInterface|null $entity */
        $entity = $repository->findOneBy(['id' => $workload['id']]);
        if (null === $entity) {
            return;
        }

        $this->contentManager->applyTransition(
            $entity,
            ['locale' => $workload['locale']],
            WorkflowInterface::WORKFLOW_TRANSITION_PUBLISH
        );
        $this->entityManager->flush();
    }
}
