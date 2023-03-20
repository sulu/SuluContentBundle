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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoDataMapper implements DataMapperInterface
{
    public function map(
        array $data,
        DimensionContentCollectionInterface $dimensionContentCollection
    ): void {
        $dimensionAttributes = $dimensionContentCollection->getDimensionAttributes();
        $unlocalizedDimensionAttributes = \array_merge($dimensionAttributes, ['locale' => null]);
        $unlocalizedObject = $dimensionContentCollection->getDimensionContent($unlocalizedDimensionAttributes);

        if (!$unlocalizedObject instanceof SeoInterface) {
            return;
        }

        $localizedObject = $dimensionContentCollection->getDimensionContent($dimensionAttributes);

        if ($localizedObject) {
            if (!$localizedObject instanceof SeoInterface) {
                throw new \RuntimeException(\sprintf('Expected "$localizedObject" from type "%s" but "%s" given.', SeoInterface::class, \get_class($localizedObject)));
            }

            $this->setSeoData($localizedObject, $data);

            return;
        }

        $this->setSeoData($unlocalizedObject, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setSeoData(SeoInterface $dimensionContent, array $data): void
    {
        $dimensionContent->setSeoTitle($data['seoTitle'] ?? null); // @phpstan-ignore-line TODO where validate this?
        $dimensionContent->setSeoDescription($data['seoDescription'] ?? null);
        $dimensionContent->setSeoKeywords($data['seoKeywords'] ?? null);
        $dimensionContent->setSeoCanonicalUrl($data['seoCanonicalUrl'] ?? null);
        $dimensionContent->setSeoHideInSitemap($data['seoHideInSitemap'] ?? false);
        $dimensionContent->setSeoNoFollow($data['seoNoFollow'] ?? false);
        $dimensionContent->setSeoNoIndex($data['seoNoIndex'] ?? false);
    }
}
