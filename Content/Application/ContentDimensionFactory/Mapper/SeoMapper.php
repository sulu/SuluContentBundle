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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMapper implements MapperInterface
{
    public function map(
        array $data,
        object $contentDimension,
        ?object $localizedContentDimension = null
    ): void {
        if (!$contentDimension instanceof SeoInterface) {
            return;
        }

        if ($localizedContentDimension) {
            if (!$localizedContentDimension instanceof SeoInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedContentDimension" from type "%s" but "%s" given.', SeoInterface::class, \get_class($localizedContentDimension)));
            }

            $this->setSeoData($localizedContentDimension, $data);

            return;
        }

        $this->setSeoData($contentDimension, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setSeoData(SeoInterface $contentDimension, array $data): void
    {
        $contentDimension->setSeoTitle($data['seoTitle'] ?? null);
        $contentDimension->setSeoDescription($data['seoDescription'] ?? null);
        $contentDimension->setSeoKeywords($data['seoKeywords'] ?? null);
        $contentDimension->setSeoCanonicalUrl($data['seoCanonicalUrl'] ?? null);
        $contentDimension->setSeoHideInSitemap($data['seoHideInSitemap'] ?? false);
        $contentDimension->setSeoNoFollow($data['seoNoFollow'] ?? false);
        $contentDimension->setSeoNoIndex($data['seoNoIndex'] ?? false);
    }
}
