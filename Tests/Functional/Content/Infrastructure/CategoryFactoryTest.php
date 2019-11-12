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

namespace Sulu\Bundle\ContentBundle\Tests\Content\Infrastructure\Doctrine;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class CategoryFactoryTest extends BaseTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
    }

    public function createCategoryFactory(): CategoryFactoryInterface
    {
        return self::$container->get('sulu_content.category_factory');
    }

    /**
     * @dataProvider dataProvider
     */
    public function testCreate($categoryIds): void
    {
        $categoryFactory = $this->createCategoryFactory();

        $this->assertSame(
            $categoryIds,
            array_map(
                function (CategoryInterface $category) {
                    return $category->getId();
                },
                $categoryFactory->create($categoryIds)
            )
        );
    }

    public function dataProvider()
    {
        yield [
            [
                // No categories
            ],
        ];

        yield [
            [
                1,
                2,
            ],
        ];
    }
}
