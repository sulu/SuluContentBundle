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
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimension;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

trait SeoDimensionTrait
{
    protected function createDraftSeoDimension(
        string $resourceKey,
        string $resourceId,
        string $locale = 'en',
        string $title = null,
        string $description = null,
        string $keywords = null,
        string $canonicalUrl = null,
        bool $noIndex = null,
        bool $noFollow = null,
        bool $hideInSitemap = null
    ): SeoDimensionInterface {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $seoDimension = new SeoDimension(
            $dimensionIdentifier,
            $resourceKey,
            $resourceId,
            $title,
            $description,
            $keywords,
            $canonicalUrl,
            $noIndex,
            $noFollow,
            $hideInSitemap
        );

        $this->getEntityManager()->persist($seoDimension);
        $this->getEntityManager()->flush();

        return $seoDimension;
    }

    protected function findDraftSeoDimension(string $resourceKey, string $resourceId, string $locale): ?SeoDimensionInterface
    {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var SeoDimensionRepositoryInterface */
        $seoDimensionRepository = $this->getEntityManager()->getRepository(SeoDimension::class);

        return $seoDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    abstract protected function findOrCreateDimensionIdentifier(array $attributes): DimensionIdentifierInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
