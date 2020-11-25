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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Sulu\SmartContent;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\Provider\ContentDataProvider;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateCategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyCategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\PublishExampleTrait;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\DataProviderResult;

class ContentDataProviderTest extends BaseTestCase
{
    use CreateExampleTrait;
    use ModifyExampleTrait;
    use PublishExampleTrait;
    use CreateCategoryTrait;
    use ModifyCategoryTrait;

    /**
     * @var ContentDataProvider
     */
    private $contentDataProvider;

    /**
     * @var CategoryInterface
     */
    private static $categoryFoo;

    /**
     * @var CategoryInterface
     */
    private static $categoryBar;

    /**
     * @var CategoryInterface
     */
    private static $categoryBaz;

    /**
     * @var TagInterface
     */
    private static $tagA;

    /**
     * @var TagInterface
     */
    private static $tagB;

    /**
     * @var TagInterface
     */
    private static $tagC;

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        static::$categoryFoo = static::createCategory('Foo', 'en');
        static::modifyCategory(static::$categoryFoo->getId(), 'Foo DE', 'de');

        static::$categoryBar = static::createCategory('Bar', 'en');

        static::$categoryBaz = static::createCategory('Baz', 'de');

        // Example 1
        $example1 = static::createExample([
            'title' => 'example without categories without tags',
            'excerptCategories' => [],
            'excerptTags' => [],
        ], 'en')->getResource();
        static::publishExample($example1->getId(), 'en');
        static::modifyExample($example1->getId(), [
            'title' => 'example without categories without tags',
            'excerptCategories' => [],
            'excerptTags' => [],
        ], 'de');
        static::publishExample($example1->getId(), 'de');
        static::modifyExample($example1->getId(), [
            'title' => 'example without categories without tags draft',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
                static::$categoryBar->getId(),
            ],
            'excerptTags' => [
                'tagB',
                'tagC',
            ],
        ], 'de');

        // Example 2
        $example2 = static::createExample([
            'title' => 'example with some categories without tags',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
            ],
            'excerptTags' => [],
        ], 'en')->getResource();
        static::publishExample($example2->getId(), 'en');
        static::modifyExample($example2->getId(), [
            'title' => 'example with some categories without tags unpublished',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
            ],
            'excerptTags' => [],
        ], 'de');

        // Example 3
        $example3 = static::createExample([
            'title' => 'example with all categories without tags',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
                static::$categoryBar->getId(),
                static::$categoryBaz->getId(),
            ],
            'excerptTags' => [],
        ], 'en')->getResource();
        static::publishExample($example3->getId(), 'en');
        static::modifyExample($example3->getId(), [
            'title' => 'example with all categories without tags',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
                static::$categoryBar->getId(),
                static::$categoryBaz->getId(),
            ],
            'excerptTags' => [],
        ], 'de');
        static::publishExample($example3->getId(), 'de');

        // Example 4
        $example4 = static::createExample([
            'title' => 'example without categories with some tags',
            'excerptCategories' => [],
            'excerptTags' => [
                'tagA',
            ],
        ], 'en')->getResource();
        static::publishExample($example4->getId(), 'en');
        static::modifyExample($example4->getId(), [
            'title' => 'example without categories with some tags',
            'excerptCategories' => [],
            'excerptTags' => [
                'tagA',
            ],
        ], 'de');
        static::publishExample($example4->getId(), 'de');

        // Example 5
        $example5 = static::createExample([
            'title' => 'example without categories with all tags',
            'excerptCategories' => [],
            'excerptTags' => [
                'tagA',
                'tagB',
                'tagC',
            ],
        ], 'en')->getResource();
        static::publishExample($example5->getId(), 'en');
        static::modifyExample($example5->getId(), [
            'title' => 'example without categories with all tags',
            'excerptCategories' => [],
            'excerptTags' => [
                'tagA',
                'tagB',
                'tagC',
            ],
        ], 'de');
        static::publishExample($example5->getId(), 'de');

        // Example 6
        $example6 = static::createExample([
            'title' => 'example with some categories with some tags',
            'excerptCategories' => [
                static::$categoryBar->getId(),
            ],
            'excerptTags' => [
                'tagB',
            ],
        ], 'en')->getResource();
        static::publishExample($example6->getId(), 'en');
        static::modifyExample($example6->getId(), [
            'title' => 'example with some categories with some tags',
            'excerptCategories' => [
                static::$categoryBar->getId(),
            ],
            'excerptTags' => [
                'tagB',
            ],
        ], 'de');
        static::publishExample($example6->getId(), 'de');

        // Example 7
        $example7 = static::createExample([
            'title' => 'example with all categories with all tags',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
                static::$categoryBar->getId(),
                static::$categoryBaz->getId(),
            ],
            'excerptTags' => [
                'tagA',
                'tagB',
                'tagC',
            ],
        ], 'en')->getResource();
        static::publishExample($example7->getId(), 'en');
        static::modifyExample($example7->getId(), [
            'title' => 'example with all categories with all tags',
            'excerptCategories' => [
                static::$categoryFoo->getId(),
                static::$categoryBar->getId(),
                static::$categoryBaz->getId(),
            ],
            'excerptTags' => [
                'tagA',
                'tagB',
                'tagC',
            ],
        ], 'de');
        static::publishExample($example7->getId(), 'de');

        // Example 8
        $example8 = static::createExample([
            'title' => 'example with non default template',
        ], 'en', 'example-2')->getResource();
        static::publishExample($example8->getId(), 'en');

        list(static::$tagA, static::$tagB, static::$tagC) = static::getContainer()->get('sulu_tag.tag_manager')->resolveTagNames(['tagA', 'tagB', 'tagC']);
    }

    public function setUp(): void
    {
        $this->contentDataProvider = $this->getContainer()->get('example_test.example_data_provider');
    }

    public function testResolveDataItems(): void
    {
        foreach ($this->filters() as $dataset) {
            list($name, $locale, $page, $pageSize, $limit, $filters, $expectedCount, $expectedHasNextPage) = $dataset;

            /** @var string $name */
            /** @var string $locale */
            /** @var int $page */
            /** @var int $pageSize */
            /** @var int $limit */
            /** @var mixed[] $filters */
            /** @var int $expectedCount */
            /** @var bool $expectedHasNextPage */
            $dataProviderResult = $this->contentDataProvider->resolveDataItems(
                $filters,
                [],
                [
                    'locale' => $locale,
                ],
                $limit,
                $page,
                $pageSize
            );

            $dataItems = $this->mapDataProviderResult($dataProviderResult);

            $this->assertCount($expectedCount, $dataItems);
            $this->assertSame($expectedHasNextPage, $dataProviderResult->getHasNextPage());
        }
    }

    public function testResolveDataItemsSnapshot(): void
    {
        /** @var int $limit */
        $limit = null;
        /** @var int $page */
        $page = null;
        /** @var int $pageSize */
        $pageSize = null;

        $dataProviderResult = $this->contentDataProvider->resolveDataItems(
            [
                'sortBy' => 'title',
                'sortMethod' => 'desc',
            ],
            [],
            [
                'locale' => 'en',
            ],
            $limit,
            $page,
            $pageSize
        );

        $dataItems = $this->mapDataProviderResult($dataProviderResult);

        $this->assertArraySnapshot('data_items.json', $dataItems);
    }

    public function testResolveResourceItemsSnapshot(): void
    {
        /** @var int $limit */
        $limit = null;
        /** @var int $page */
        $page = null;
        /** @var int $pageSize */
        $pageSize = null;

        $dataProviderResult = $this->contentDataProvider->resolveResourceItems(
            [
                'sortBy' => 'title',
                'sortMethod' => 'asc',
            ],
            [],
            [
                'locale' => 'de',
            ],
            $limit,
            $page,
            $pageSize
        );

        $resourceItems = $this->mapDataProviderResult($dataProviderResult);

        $this->assertArraySnapshot('resource_items.json', $resourceItems);
    }

    /**
     * This method can't be a phpunit dataProvider, because then it wouldn't be possible to access the categories, because they don't exist at the time a dataProvider is called.
     *
     * @return mixed[]
     */
    public function filters(): array
    {
        return [
            [
                'noFilters',
                'en',
                null,
                null,
                null,
                [],
                8,
                false,
            ],
            [
                'noFiltersLimited',
                'en',
                null,
                null,
                5,
                [],
                5,
                false,
            ],
            [
                'noFiltersPaginated',
                'en',
                2,
                2,
                3,
                [],
                1,
                false,
            ],
            [
                'noFiltersPaginatedWithoutLimit',
                'en',
                3,
                2,
                null,
                [],
                2,
                true,
            ],
            [
                'withAllCategoriesOR',
                'en',
                null,
                null,
                null,
                [
                    'categories' => [
                        static::$categoryFoo->getId(),
                        static::$categoryBar->getId(),
                        static::$categoryBaz->getId(),
                    ],
                    'categoryOperator' => 'OR',
                ],
                4,
                false,
            ],
            [
                'withAllTagsOR',
                'en',
                null,
                null,
                null,
                [
                    'tags' => [
                        static::$tagA,
                        static::$tagB,
                        static::$tagC,
                    ],
                    'tagOperator' => 'OR',
                ],
                4,
                false,
            ],
            [
                'withAllCategoriesORAllTagsOR',
                'en',
                null,
                null,
                null,
                [
                    'categories' => [
                        static::$categoryFoo->getId(),
                        static::$categoryBar->getId(),
                        static::$categoryBaz->getId(),
                    ],
                    'categoryOperator' => 'OR',
                    'tags' => [
                        static::$tagA,
                        static::$tagB,
                        static::$tagC,
                    ],
                    'tagOperator' => 'OR',
                ],
                2,
                false,
            ],
            [
                'withSomeCategoriesANDSomeWebsiteCategoriesOR',
                'en',
                null,
                null,
                null,
                [
                    'categories' => [
                        static::$categoryFoo->getId(),
                    ],
                    'categoryOperator' => 'AND',
                    'websiteCategories' => [
                        static::$categoryBar->getId(),
                        static::$categoryBaz->getId(),
                    ],
                    'websiteCategoriesOperator' => 'OR',
                ],
                2,
                false,
            ],
            [
                'withAllCategories',
                'en',
                null,
                null,
                null,
                [
                    'categories' => [
                        static::$categoryFoo->getId(),
                    ],
                    'websiteCategories' => [
                        static::$categoryBar->getId(),
                        static::$categoryBaz->getId(),
                    ],
                    'categoryOperator' => 'AND',
                    'websiteCategoriesOperator' => 'AND',
                ],
                2,
                false,
            ],
            [
                'withAllTags',
                'en',
                null,
                null,
                null,
                [
                    'tags' => [
                        static::$tagA,
                        static::$tagB,
                    ],
                    'websiteTags' => [
                        static::$tagC,
                    ],
                    'tagOperator' => 'AND',
                    'websiteTagsOperator' => 'AND',
                ],
                2,
                false,
            ],
            [
                'withAllCategoriesAndAllTags',
                'en',
                null,
                null,
                null,
                [
                    'categories' => [
                        static::$categoryFoo->getId(),
                        static::$categoryBar->getId(),
                        static::$categoryBaz->getId(),
                    ],
                    'categoryOperator' => 'AND',
                    'tags' => [
                        static::$tagA,
                        static::$tagB,
                        static::$tagC,
                    ],
                    'tagOperator' => 'AND',
                ],
                1,
                false,
            ],
            [
                'withOneType',
                'en',
                null,
                null,
                null,
                [
                    'types' => [
                        'example-2',
                    ],
                ],
                1,
                false,
            ],
            [
                'withAllTypes',
                'en',
                null,
                null,
                null,
                [
                    'types' => [
                        'default',
                        'example-2',
                    ],
                ],
                8,
                false,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    private function mapDataProviderResult(DataProviderResult $dataProviderResult): array
    {
        return array_map(function (ArrayAccessItem $item) {
            return [
                'id' => $item->getId(),
                'excerptCategories' => $item['excerptCategories'],
                'excerptTags' => $item['excerptTags'],
                'published' => $item['published'],
                'publishedState' => $item['publishedState'],
                'title' => $item['title'],
                'url' => $item['url'],
            ];
        }, $dataProviderResult->getItems());
    }
}
