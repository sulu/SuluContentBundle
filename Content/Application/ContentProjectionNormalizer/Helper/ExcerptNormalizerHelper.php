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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\Helper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptNormalizerHelper implements NormalizerHelperInterface
{
    public function normalize(object $object, array $viewData): array
    {
        if (!$object instanceof ExcerptInterface) {
            return $viewData;
        }

        $viewData['excerptTags'] = $viewData['excerptTagNames'];
        unset($viewData['excerptTagNames']);
        $viewData['excerptCategories'] = $viewData['excerptCategoryIds'];
        unset($viewData['excerptCategoryIds']);

        return $viewData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        return [
            'excerptTags',
            'excerptCategories',
        ];
    }
}
