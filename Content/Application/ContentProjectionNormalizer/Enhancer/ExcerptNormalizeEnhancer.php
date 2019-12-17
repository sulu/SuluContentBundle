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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Enhancer;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptNormalizeEnhancer implements NormalizeEnhancerInterface
{
    public function enhance(object $object, array $normalizeData): array
    {
        if (!$object instanceof ExcerptInterface) {
            return $normalizeData;
        }

        $normalizeData['excerptTags'] = $normalizeData['excerptTagNames'];
        unset($normalizeData['excerptTagNames']);
        $normalizeData['excerptCategories'] = $normalizeData['excerptCategoryIds'];
        unset($normalizeData['excerptCategoryIds']);

        return $normalizeData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof ExcerptInterface) {
            return [];
        }

        return [
            'excerptTags',
            'excerptCategories',
        ];
    }
}
