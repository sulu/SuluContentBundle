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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Traits;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\Collection;
use Sulu\Bundle\MediaBundle\Entity\CollectionInterface;
use Sulu\Bundle\MediaBundle\Entity\CollectionType;
use Sulu\Bundle\MediaBundle\Entity\Media;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaType;

trait MediaTrait
{
    public function createCollectionType(string $name): CollectionType
    {
        $collectionType = new CollectionType();
        $collectionType->setName($name);

        $this->getEntityManager()->persist($collectionType);
        $this->getEntityManager()->flush();

        return $collectionType;
    }

    public function createCollection(CollectionType $collectionType): CollectionInterface
    {
        $collection = new Collection();
        $collection->setType($collectionType);

        $this->getEntityManager()->persist($collection);
        $this->getEntityManager()->flush();

        return $collection;
    }

    private function createMediaType(string $name): MediaType
    {
        $mediaType = new MediaType();
        $mediaType->setName($name);

        $this->getEntityManager()->persist($mediaType);
        $this->getEntityManager()->flush();

        return $mediaType;
    }

    private function createMedia(
        MediaType $mediaType,
        CollectionInterface $collection
    ): MediaInterface {
        $media = new Media();
        $media->setType($mediaType);
        $media->setCollection($collection);

        $this->getEntityManager()->persist($media);
        $this->getEntityManager()->flush();

        return $media;
    }

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
