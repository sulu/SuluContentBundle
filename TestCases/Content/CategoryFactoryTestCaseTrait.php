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

namespace Sulu\Bundle\ContentBundle\TestCases\Content;

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;

trait CategoryFactoryTestCaseTrait
{
    abstract protected function createCategoryFactory(): CategoryFactoryInterface;

    /**
     * @dataProvider dataProvider
     */
    public function testCreate($categoryIds): void
    {
        $tagFactory = $this->createCategoryFactory();

        $this->assertSame(
            $categoryIds,
            array_map(
                function (CategoryInterface $category) {
                    return $category->getId();
                },
                $tagFactory->create($categoryIds)
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
