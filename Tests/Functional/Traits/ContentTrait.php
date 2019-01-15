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
use Sulu\Bundle\ContentBundle\Model\Content\Content;
use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

trait ContentTrait
{
    protected function createContent(
        string $resourceKey,
        string $resourceId,
        string $locale = 'en',
        ?string $type = 'default',
        array $data = ['title' => 'Sulu', 'article' => 'Sulu is awesome']
    ): ContentInterface {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );
        $content = new Content($dimension, $resourceKey, $resourceId, $type, $data);

        $this->getEntityManager()->persist($content);
        $this->getEntityManager()->flush();

        return $content;
    }

    protected function findContent(string $resourceKey, string $resourceId, string $locale): ?ContentInterface
    {
        $dimension = $this->findDimension(
            [
                DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT,
                DimensionInterface::ATTRIBUTE_KEY_LOCALE => $locale,
            ]
        );

        /** @var Content $content */
        $content = $this->getEntityManager()->find(
            Content::class,
            ['resourceKey' => $resourceKey, 'resourceId' => $resourceId, 'dimension' => $dimension]
        );

        return $content;
    }

    abstract protected function findDimension(array $attributes): DimensionInterface;

    /**
     * @return EntityManagerInterface
     */
    abstract protected function getEntityManager();
}
