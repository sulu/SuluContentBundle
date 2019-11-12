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

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TagBundle\Tag\TagRepositoryInterface;

class TagFactoryTest extends BaseTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
    }

    protected function createTagFactory(array $existTagNames = []): TagFactoryInterface
    {
        /** @var TagRepositoryInterface $tagRepository */
        $tagRepository = self::$container->get('sulu.repository.tag');

        foreach ($existTagNames as $existTagName) {
            $existTag = $tagRepository->createNew();
            $existTag->setName($existTagName);
            self::getEntityManager()->persist($existTag);
        }

        if (\count($existTagNames)) {
            self::getEntityManager()->flush();
            self::getEntityManager()->clear();
        }

        return self::$container->get('sulu_content.tag_factory');
    }

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
