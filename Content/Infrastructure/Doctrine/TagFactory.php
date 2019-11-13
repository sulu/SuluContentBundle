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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Component\Persistence\Repository\ORM\EntityRepository;

class TagFactory implements TagFactoryInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var EntityRepository
     */
    private $tagRepository;

    public function __construct(EntityManagerInterface $entityManager, EntityRepository $tagRepository)
    {
        $this->entityManager = $entityManager;
        $this->tagRepository = $tagRepository;
    }

    public function create(array $tagNames): array
    {
        if (empty($tagNames)) {
            return [];
        }

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(TagInterface::class, 'tag')
            ->select('tag');
        $queryBuilder->where($queryBuilder->expr()->in('tag.name', $tagNames));
        /** @var iterable<TagInterface> $tags */
        $tags = $queryBuilder->getQuery()->getResult();

        // sort tags by the given names order
        $excerptTags = [];
        foreach ($tags as $tag) {
            $excerptTags[array_search($tag->getName(), $tagNames, true)] = $tag;
        }

        // create tags which not exist yet
        foreach ($tagNames as $key => $tagName) {
            if (isset($excerptTags[$key])) {
                continue;
            }

            /** @var TagInterface $tag */
            $tag = $this->tagRepository->createNew();
            $tag->setName($tagName);

            $this->entityManager->persist($tag);

            $excerptTags[$key] = $tag;
        }

        return $excerptTags;
    }
}
