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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimension;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\IconReferenceInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReference;
use Sulu\Bundle\ContentBundle\Model\Excerpt\TagReferenceInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ExcerptDimensionTrait
{
    /**
     * @param CategoryInterface[] $categories
     * @param TagInterface[] $tags
     * @param MediaInterface[] $icons
     * @param MediaInterface[] $images
     */
    protected function createDraftExcerptDimension(
        string $resourceKey,
        string $resourceId,
        string $locale = 'en',
        ?string $title = null,
        ?string $more = null,
        ?string $description = null,
        array $categories = [],
        array $tags = [],
        array $icons = [],
        array $images = []
    ): ExcerptDimensionInterface {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $excerptDimension = new ExcerptDimension(
            $dimensionIdentifier,
            $resourceKey,
            $resourceId,
            $title,
            $more,
            $description
        );
        $this->getEntityManager()->persist($excerptDimension);

        foreach ($categories as $category) {
            $excerptDimension->addCategory($category);
        }
        foreach ($tags as $index => $tag) {
            $tagReference = $this->createTagReference($excerptDimension, $tag, $index);
            $excerptDimension->addTag($tagReference);
        }
        foreach ($icons as $index => $icon) {
            $iconReference = $this->createIconReference($excerptDimension, $icon, $index);
            $excerptDimension->addIcon($iconReference);
        }
        foreach ($images as $image) {
            $excerptDimension->addImage($image);
        }

        $this->getEntityManager()->flush();

        return $excerptDimension;
    }

    protected function createTagReference(
        ExcerptDimensionInterface $excerptDimension,
        TagInterface $tag,
        int $order
    ): TagReferenceInterface {
        $tagReference = new TagReference($excerptDimension, $tag, $order);

        $this->getEntityManager()->persist($tagReference);
        $this->getEntityManager()->flush();

        return $tagReference;
    }

    protected function createIconReference(
        ExcerptDimensionInterface $excerptDimension,
        MediaInterface $media,
        int $order
    ): IconReferenceInterface {
        $iconReference = new IconReference($excerptDimension, $media, $order);

        $this->getEntityManager()->persist($iconReference);
        $this->getEntityManager()->flush();

        return $iconReference;
    }

    protected function findDraftExcerptDimension(string $resourceKey, string $resourceId, string $locale): ?ExcerptDimensionInterface
    {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var ExcerptDimensionRepositoryInterface */
        $excerptDimensionRepository = $this->getEntityManager()->getRepository(ExcerptDimension::class);

        return $excerptDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    abstract protected function findOrCreateDimensionIdentifier(array $attributes): DimensionIdentifierInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
