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
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

trait ContentDimensionTrait
{
    protected function createDraftContentDimension(
        string $resourceKey,
        string $resourceId,
        string $locale = 'en',
        ?string $type = 'default',
        array $data = ['title' => 'Sulu', 'article' => 'Sulu is awesome']
    ): ContentDimensionInterface {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $contentDimension = new ContentDimension($dimensionIdentifier, $resourceKey, $resourceId, $type, $data);

        $this->getEntityManager()->persist($contentDimension);
        $this->getEntityManager()->flush();

        return $contentDimension;
    }

    protected function findDraftContentDimension(string $resourceKey, string $resourceId, string $locale): ?ContentDimensionInterface
    {
        $dimensionIdentifier = $this->findOrCreateDimensionIdentifier(
            [
                DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var ContentDimensionRepositoryInterface */
        $contentDimensionRepository = $this->getEntityManager()->getRepository(ContentDimension::class);

        return $contentDimensionRepository->findDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    abstract protected function findOrCreateDimensionIdentifier(array $attributes): DimensionIdentifierInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
