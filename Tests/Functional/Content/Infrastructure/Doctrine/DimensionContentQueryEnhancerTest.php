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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Doctrine;

use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Repository\ExampleRepository;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateCategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CreateTagTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class DimensionContentQueryEnhancerTest extends SuluTestCase
{
    use CreateCategoryTrait;
    use CreateExampleTrait;
    use CreateTagTrait;

    /**
     * @var ExampleRepository
     */
    private $exampleRepository;

    protected function setUp(): void
    {
        $this->exampleRepository = static::getContainer()->get('example_test.example_repository');
    }

    public function testNullDimensionAttribute(): void
    {
        static::purgeDatabase();

        $example = static::createExample();
        $example2 = static::createExample();
        $example3 = static::createExample();
        static::createExampleContent($example, ['title' => 'Example A']);
        static::createExampleContent($example, ['title' => 'Example A', 'stage' => 'live']);
        static::createExampleContent($example2, ['title' => 'Example B']);
        static::createExampleContent($example3, ['title' => 'Example C']);
        static::createExampleContent($example3, ['title' => 'Example C', 'stage' => 'live']);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $examples = iterator_to_array($this->exampleRepository->findBy(['locale' => null, 'stage' => 'draft']));
        $this->assertCount(3, $examples);
    }

    public function testInvalidOperator(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        iterator_to_array($this->exampleRepository->findBy(['locale' => 'en', 'stage' => 'live', 'tagNames' => ['A', 'B'], 'tagOperator' => 'INVALID'])); // @phpstan-ignore-line
    }

    public function testFindByLocaleAndStage(): void
    {
        static::purgeDatabase();

        $example = static::createExample();
        $example2 = static::createExample();
        $example3 = static::createExample();
        static::createExampleContent($example, ['title' => 'Example A']);
        static::createExampleContent($example, ['title' => 'Example A', 'stage' => 'live']);
        static::createExampleContent($example2, ['title' => 'Example B']);
        static::createExampleContent($example3, ['title' => 'Example C']);
        static::createExampleContent($example3, ['title' => 'Example C', 'stage' => 'live']);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $examples = iterator_to_array($this->exampleRepository->findBy(['locale' => 'en', 'stage' => 'live']));
        $this->assertCount(2, $examples);
    }

    public function testCategoryFilters(): void
    {
        static::purgeDatabase();

        $categoryA = static::createCategory(['key' => 'a']);
        $categoryB = static::createCategory(['key' => 'b']);

        $example = static::createExample();
        $example2 = static::createExample();
        $example3 = static::createExample();
        static::createExampleContent($example, ['title' => 'Example A', 'excerptCategories' => [$categoryA]]);
        static::createExampleContent($example2, ['title' => 'Example B']);
        static::createExampleContent($example3, ['title' => 'Example C', 'excerptCategories' => [$categoryA, $categoryB]]);
        static::getEntityManager()->flush();
        $categoryAId = $categoryA->getId();
        $categoryBId = $categoryB->getId();
        static::getEntityManager()->clear();

        $this->assertCount(2, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryKeys' => ['a', 'b'],
        ])));

        $this->assertSame(2, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryKeys' => ['a', 'b'],
        ]));

        $this->assertCount(1, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryKeys' => ['a', 'b'],
            'categoryOperator' => 'AND',
        ])));

        $this->assertSame(1, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryKeys' => ['a', 'b'],
            'categoryOperator' => 'AND',
        ]));

        $this->assertCount(2, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryIds' => [$categoryAId, $categoryBId],
        ])));

        $this->assertSame(2, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryIds' => [$categoryAId, $categoryBId],
        ]));

        $this->assertCount(1, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryIds' => [$categoryAId, $categoryBId],
            'categoryOperator' => 'AND',
        ])));

        $this->assertSame(1, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'categoryIds' => [$categoryAId, $categoryBId],
            'categoryOperator' => 'AND',
        ]));
    }

    public function testTagFilters(): void
    {
        static::purgeDatabase();

        $tagA = static::createTag(['name' => 'a']);
        $tagB = static::createTag(['name' => 'b']);

        $example = static::createExample();
        $example2 = static::createExample();
        $example3 = static::createExample();
        static::createExampleContent($example, ['title' => 'Example A', 'excerptTags' => [$tagA]]);
        static::createExampleContent($example2, ['title' => 'Example B']);
        static::createExampleContent($example3, ['title' => 'Example C', 'excerptTags' => [$tagA, $tagB]]);
        static::getEntityManager()->flush();
        $tagAId = $tagA->getId();
        $tagBId = $tagB->getId();
        static::getEntityManager()->clear();

        $this->assertCount(2, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagNames' => ['a', 'b'],
        ])));

        $this->assertSame(2, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagNames' => ['a', 'b'],
        ]));

        $this->assertCount(1, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagNames' => ['a', 'b'],
            'tagOperator' => 'AND',
        ])));

        $this->assertSame(1, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagNames' => ['a', 'b'],
            'tagOperator' => 'AND',
        ]));

        $this->assertCount(2, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagIds' => [$tagAId, $tagBId],
        ])));

        $this->assertSame(2, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagIds' => [$tagAId, $tagBId],
        ]));

        $this->assertCount(1, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagIds' => [$tagAId, $tagBId],
            'tagOperator' => 'AND',
        ])));

        $this->assertSame(1, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'tagIds' => [$tagAId, $tagBId],
            'tagOperator' => 'AND',
        ]));
    }

    public function testFilterTemplateKeys(): void
    {
        static::purgeDatabase();

        $example = static::createExample();
        $example2 = static::createExample();
        $example3 = static::createExample();
        static::createExampleContent($example, ['title' => 'Example A', 'templateKey' => 'a']);
        static::createExampleContent($example2, ['title' => 'Example B', 'templateKey' => 'b']);
        static::createExampleContent($example3, ['title' => 'Example C', 'templateKey' => 'c']);
        static::getEntityManager()->flush();
        static::getEntityManager()->clear();

        $this->assertCount(2, iterator_to_array($this->exampleRepository->findBy([
            'locale' => 'en',
            'stage' => 'draft',
            'templateKeys' => ['a', 'c'],
        ])));

        $this->assertSame(2, $this->exampleRepository->countBy([
            'locale' => 'en',
            'stage' => 'draft',
            'templateKeys' => ['a', 'c'],
        ]));
    }
}
