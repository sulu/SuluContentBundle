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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\DimensionContentQueryEnhancer;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Exception\ExampleNotFoundException;
use Webmozart\Assert\Assert;

class ExampleRepository
{
    public const GROUP_CONTENT_EDIT = 'example_content_edit';
    public const GROUP_CONTENT_WEBSITE = 'example_content_website';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository<Example>
     */
    private $entityRepository;

    /**
     * @var DimensionContentQueryEnhancer
     */
    private $dimensionContentQueryEnhancer;

    public function __construct(
        EntityManagerInterface $entityManager,
        DimensionContentQueryEnhancer $dimensionContentQueryEnhancer
    ) {
        $this->entityRepository = $entityManager->getRepository(Example::class);
        $this->entityManager = $entityManager;
        $this->dimensionContentQueryEnhancer = $dimensionContentQueryEnhancer;
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     * } $filters
     * @param array{
     *     context?: string,
     * } $options
     *
     * @throws ExampleNotFoundException
     */
    public function getOneBy(array $filters, array $options = []): Example
    {
        $queryBuilder = $this->createQueryBuilder($filters, [], $options);

        try {
            /** @var Example $example */
            $example = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            throw new ExampleNotFoundException($filters, 0, $e);
        }

        return $example;
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     * } $filters
     * @param array{
     *     context?: string,
     * } $options
     */
    public function findOneBy(array $filters, array $options = []): ?Example
    {
        $queryBuilder = $this->createQueryBuilder($filters, [], $options);

        try {
            /** @var Example $example */
            $example = $queryBuilder->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            return null;
        }

        return $example;
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     * } $filters
     */
    public function countBy(array $filters = []): int
    {
        // The countBy method will ignore any page and limit parameters
        // for better developer experience we will strip them away here
        // instead of that the developer need to take that into account
        // in there call of the countBy method.
        unset($filters['page']); // @phpstan-ignore-line
        unset($filters['limit']);

        /**
         * @see https://github.com/phpstan/phpstan/issues/5223https://github.com/phpstan/phpstan/issues/5223
         *
         * @var array{
         *     id?: int,
         *     ids?: int[],
         *     locale?: string|null,
         *     stage?: string|null,
         *     categoryIds?: int[],
         *     categoryKeys?: string[],
         *     categoryOperator?: 'AND'|'OR',
         *     tagIds?: int[],
         *     tagNames?: string[],
         *     tagOperator?: 'AND'|'OR',
         *     templateKeys?: string[],
         * } $filters */

        $queryBuilder = $this->createQueryBuilder($filters);

        $queryBuilder->select('COUNT(DISTINCT example.id)');

        return (int) $queryBuilder->getQuery()->getSingleScalarResult();
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     page?: int,
     *     limit?: int,
     * } $filters
     * @param array{
     *     id?: 'asc'|'desc',
     *     title?: 'asc'|'desc',
     * } $sortBy
     *
     * @return \Generator<Example>
     */
    public function findBy(array $filters = [], array $sortBy = []): \Generator
    {
        $queryBuilder = $this->createQueryBuilder($filters, $sortBy);

        // TODO optimize hydration with toIterable()
        /** @var iterable<Example> $examples */
        $examples = $queryBuilder->getQuery()->getResult();

        foreach ($examples as $example) {
            yield $example;
        }
    }

    public function add(Example $example): void
    {
        $this->entityManager->persist($example);
    }

    public function remove(Example $example): void
    {
        $this->entityManager->remove($example);
    }

    /**
     * @param array{
     *     id?: int,
     *     ids?: int[],
     *     locale?: string|null,
     *     stage?: string|null,
     *     categoryIds?: int[],
     *     categoryKeys?: string[],
     *     categoryOperator?: 'AND'|'OR',
     *     tagIds?: int[],
     *     tagNames?: string[],
     *     tagOperator?: 'AND'|'OR',
     *     templateKeys?: string[],
     *     page?: int,
     *     limit?: int,
     * } $filters
     * @param array{
     *     id?: 'asc'|'desc',
     *     title?: 'asc'|'desc',
     * } $sortBy
     * @param array{
     *     context?: string,
     * } $options
     */
    private function createQueryBuilder(array $filters, array $sortBy = [], array $options = []): QueryBuilder
    {
        $queryBuilder = $this->entityRepository->createQueryBuilder('example');

        $id = $filters['id'] ?? null;
        if (null !== $id) {
            Assert::integer($id);
            $queryBuilder->andWhere('example.id = :id')
                ->setParameter('id', $id);
        }

        $ids = $filters['ids'] ?? null;
        if (null !== $ids) {
            Assert::isArray($ids);
            $queryBuilder->andWhere('example.id IN(:ids)')
                ->setParameter('ids', $ids);
        }

        $limit = $filters['limit'] ?? null;
        if (null !== $limit) {
            Assert::integer($limit);
            $queryBuilder->setMaxResults($limit);
        }

        $page = $filters['page'] ?? null;
        if (null !== $page) {
            Assert::notNull($limit);
            Assert::integer($page);
            $offset = (int) ($limit * ($page - 1));
            $queryBuilder->setFirstResult($offset);
        }

        $contentFilters = [];
        foreach ([
            'locale',
            'stage',
            'categoryIds',
            'categoryKeys',
            'categoryOperator',
            'tagIds',
            'tagNames',
            'tagOperator',
            'templateKeys',
        ] as $key) {
            if (\array_key_exists($key, $filters)) {
                $contentFilters[$key] = $filters[$key];
            }
        }

        /**
         * @see https://github.com/phpstan/phpstan/issues/5223https://github.com/phpstan/phpstan/issues/5223
         *
         * @var array{
         *     locale?: string|null,
         *     stage?: string|null,
         *     categoryIds?: int[],
         *     categoryKeys?: string[],
         *     categoryOperator?: 'AND'|'OR',
         *     tagIds?: int[],
         *     tagNames?: string[],
         *     tagOperator?: 'AND'|'OR',
         *     templateKeys?: string[],
         * } $contentFilters
         */

        if (!empty($contentFilters)) {
            Assert::keyExists($contentFilters, 'locale');
            Assert::keyExists($contentFilters, 'stage');

            $this->dimensionContentQueryEnhancer->addFilters(
                $queryBuilder,
                'example',
                ExampleDimensionContent::class,
                $contentFilters
            );
        }

        return $queryBuilder;
    }
}
