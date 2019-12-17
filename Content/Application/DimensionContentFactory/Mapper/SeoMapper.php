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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMapper implements MapperInterface
{
    public function map(
        array $data,
        object $dimensionContent,
        ?object $localizedDimensionContent = null
    ): void {
        if (!$dimensionContent instanceof SeoInterface) {
            return;
        }

        if ($localizedDimensionContent) {
            if (!$localizedDimensionContent instanceof SeoInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedDimensionContent" from type "%s" but "%s" given.', SeoInterface::class, \get_class($localizedDimensionContent)));
            }

            $this->setSeoData($localizedDimensionContent, $data);

            return;
        }

        $this->setSeoData($dimensionContent, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setSeoData(SeoInterface $dimensionContent, array $data): void
    {
        $dimensionContent->setSeoTitle($data['seoTitle'] ?? null);
        $dimensionContent->setSeoDescription($data['seoDescription'] ?? null);
        $dimensionContent->setSeoKeywords($data['seoKeywords'] ?? null);
        $dimensionContent->setSeoCanonicalUrl($data['seoCanonicalUrl'] ?? null);
        $dimensionContent->setSeoHideInSitemap($data['seoHideInSitemap'] ?? false);
        $dimensionContent->setSeoNoFollow($data['seoNoFollow'] ?? false);
        $dimensionContent->setSeoNoIndex($data['seoNoIndex'] ?? false);
    }
}
