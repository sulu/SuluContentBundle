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

namespace Sulu\Bundle\ContentBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class TagReferenceRepository extends ServiceEntityRepository implements TagReferenceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TagReference::class);
    }

    public function create(ExcerptDimensionInterface $excerptDimension, TagInterface $tag): TagReferenceInterface
    {
        $className = $this->getClassName();
        $tagReference = new $className($excerptDimension, $tag);

        $this->getEntityManager()->persist($tagReference);

        return $tagReference;
    }

    public function remove(TagReferenceInterface $tagReference): void
    {
        $this->getEntityManager()->remove($tagReference);
    }
}
