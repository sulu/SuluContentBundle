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

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait TagFactoryTestCaseTrait
{
    abstract protected function createTagFactory(array $existTagNames = []): TagFactoryInterface;

    /**
     * @dataProvider dataProvider
     */
    public function testCreate($tagNames, $existTags): void
    {
        $tagFactory = $this->createTagFactory($existTags);

        $this->assertSame(
            $tagNames,
            array_map(
                function (TagInterface $tag) {
                    return $tag->getName();
                },
                $tagFactory->create($tagNames)
            )
        );
    }

    public function dataProvider()
    {
        yield [
            [
                // No tags
            ],
            [
                // No exist tags
            ],
        ];

        yield [
            [
                'Tag 1',
                'Tag 2',
            ],
            [
                // No exist tags
            ],
        ];

        yield [
            [
                'Exist Tag 1',
                'Tag 2',
            ],
            [
                'Exist Tag 1',
            ],
        ];

        yield [
            [
                'Exist Tag 1',
                'Exist Tag 2',
            ],
            [
                'Exist Tag 1',
                'Exist Tag 2',
            ],
        ];
    }
}
