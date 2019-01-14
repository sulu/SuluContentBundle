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
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimension;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

trait ContentDimensionTrait
{
    protected function createContentDimension(
        string $resourceKey,
        string $resourceId,
        string $locale = 'en',
        ?string $type = 'default',
        array $data = ['title' => 'Sulu', 'article' => 'Sulu is awesome']
    ): ContentDimensionInterface {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $contentDimension = new ContentDimension($dimension, $resourceKey, $resourceId, $type, $data);

        $this->getEntityManager()->persist($contentDimension);
        $this->getEntityManager()->flush();

        return $contentDimension;
    }

    protected function findContentDimension(string $resourceKey, string $resourceId, string $locale): ?ContentDimensionInterface
    {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var ContentDimension $contentDimension */
        $contentDimension = $this->getEntityManager()->find(
            ContentDimension::class,
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]
        );

        return $contentDimension;
    }

    abstract protected function findDimension(array $attributes): DimensionInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
