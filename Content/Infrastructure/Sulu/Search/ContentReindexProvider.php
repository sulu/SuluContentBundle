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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Search;

use Doctrine\ORM\EntityManagerInterface;
use Massive\Bundle\SearchBundle\Search\Reindex\LocalizedReindexProviderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Component\HttpKernel\SuluKernel;

class ContentReindexProvider implements LocalizedReindexProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var string
     */
    private $context;

    /**
     * @var class-string<ContentRichEntityInterface>
     */
    private $contentRichEntityClass;

    /**
     * @var class-string<DimensionContentInterface>|null
     */
    private $dimensionContentClass = null;

    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        string $context,
        string $contentRichEntityClass
    ) {
        $this->entityManager = $entityManager;
        $this->contentResolver = $contentResolver;
        $this->context = $context;
        $this->contentRichEntityClass = $contentRichEntityClass;
    }

    public function provide($classFqn, $offset, $maxResults)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($this->contentRichEntityClass, 'contentRichEntity')
            ->select('contentRichEntity')
            ->setFirstResult($offset)
            ->setMaxResults($maxResults);

        return $queryBuilder->getQuery()->execute();
    }

    public function cleanUp($classFqn): void
    {
        $this->entityManager->clear();
    }

    public function getCount($classFqn)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from($this->contentRichEntityClass, 'contentRichEntity')
            ->select('COUNT(contentRichEntity)');

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @return string[]
     */
    public function getClassFqns()
    {
        return [$this->getDimensionContentClass()];
    }

    public function getLocalesForObject($object)
    {
        if (!$object instanceof ContentRichEntityInterface) {
            return [];
        }

        $stage = $this->getWorkflowStage();

        $locales = $object->getDimensionContents()
            ->filter(
                function (DimensionContentInterface $dimensionContent) use ($stage) {
                    return $stage === $dimensionContent->getDimension()->getStage();
                }
            )
            ->map(
                function (DimensionContentInterface $dimensionContent) {
                    return $dimensionContent->getDimension()->getLocale();
                }
            )->getValues();

        return array_values(array_filter(array_unique($locales)));
    }

    /**
     * @return object|DimensionContentInterface|null
     */
    public function translateObject($object, $locale)
    {
        if (!$object instanceof ContentRichEntityInterface) {
            return $object;
        }

        $stage = $this->getWorkflowStage();

        try {
            $dimensionContent = $this->contentResolver->resolve(
                $object,
                [
                    'locale' => $locale,
                    'stage' => $stage,
                ]
            );
        } catch (ContentNotFoundException $e) {
            return null;
        }

        if ($stage !== $dimensionContent->getDimension()->getStage()
            || $locale !== $dimensionContent->getDimension()->getLocale()) {
            return null;
        }

        return $dimensionContent;
    }

    private function getWorkflowStage(): string
    {
        $interfaces = class_implements($this->getDimensionContentClass());

        if ($interfaces && \in_array(WorkflowInterface::class, $interfaces, true)
            && SuluKernel::CONTEXT_WEBSITE === $this->context) {
            return DimensionInterface::STAGE_LIVE;
        }

        return DimensionInterface::STAGE_DRAFT;
    }

    /**
     * @return class-string<DimensionContentInterface>
     */
    private function getDimensionContentClass(): string
    {
        if (null !== $this->dimensionContentClass) {
            return $this->dimensionContentClass;
        }

        $classMetadata = $this->entityManager->getClassMetadata($this->contentRichEntityClass);
        $associationMapping = $classMetadata->getAssociationMapping('dimensionContents');
        $this->dimensionContentClass = $associationMapping['targetEntity'];

        return $this->dimensionContentClass;
    }
}
