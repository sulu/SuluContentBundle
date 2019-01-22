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
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ImageReferenceRepositoryInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class ImageReferenceRepository extends ServiceEntityRepository implements ImageReferenceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ImageReference::class);
    }

    public function create(
        ExcerptDimensionInterface $excerptDimension,
        MediaInterface $media,
        int $order = 0
    ): ImageReferenceInterface {
        $className = $this->getClassName();
        $imageReference = new $className($excerptDimension, $media, $order);
        $this->getEntityManager()->persist($imageReference);

        return $imageReference;
    }

    public function remove(ImageReferenceInterface $imageReference): void
    {
        $this->getEntityManager()->remove($imageReference);
    }
}
