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
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Seo;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoInterface;

trait SeoTrait
{
    protected function createSeo(
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
    ): SeoInterface {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $seo = new Seo(
            $dimension,
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

        $this->getEntityManager()->persist($seo);
        $this->getEntityManager()->flush();

        return $seo;
    }

    protected function findSeo(string $resourceKey, string $resourceId, string $locale): ?SeoInterface
    {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var Seo $seo */
        $seo = $this->getEntityManager()->find(
            Seo::class,
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]
        );

        return $seo;
    }

    abstract protected function findDimension(array $attributes): DimensionInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
