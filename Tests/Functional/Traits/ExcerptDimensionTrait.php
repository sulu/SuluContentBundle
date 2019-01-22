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

        foreach ($categories as $category) {
            $excerptDimension->addCategory($category);
        }
        foreach ($tags as $tag) {
            $excerptDimension->addTag($tag);
        }
        foreach ($icons as $icon) {
            $excerptDimension->addIcon($icon);
        }
        foreach ($images as $image) {
            $excerptDimension->addImage($image);
        }

        $this->getEntityManager()->persist($excerptDimension);
        $this->getEntityManager()->flush();

        return $excerptDimension;
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
