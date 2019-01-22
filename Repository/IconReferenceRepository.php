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
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceRepositoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class IconReferenceRepository extends ServiceEntityRepository implements IconReferenceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IconReference::class);
    }

    public function create(
        ExcerptDimensionInterface $excerptDimension,
        MediaInterface $media,
        int $order = 0
    ): IconReferenceInterface {
        $className = $this->getClassName();
        $iconReference = new $className($excerptDimension, $media, $order);
        $this->getEntityManager()->persist($iconReference);

        return $iconReference;
    }

    public function remove(IconReferenceInterface $iconReference): void
    {
        $this->getEntityManager()->remove($iconReference);
    }
}
