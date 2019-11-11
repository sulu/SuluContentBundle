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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptResolver implements ResolverInterface
{
    public function resolve(object $contentView, array $viewData): array
    {
        if (!$contentView instanceof ExcerptInterface) {
            return $viewData;
        }

        $viewData['excerptTags'] = $viewData['excerptTagNames'];
        unset($viewData['excerptTagNames']);
        $viewData['excerptCategories'] = $viewData['excerptCategoryIds'];
        unset($viewData['excerptCategoryIds']);

        return $viewData;
    }

    public function getIgnoreAttributes(object $contentView): array
    {
        return [
            'excerptTags',
            'excerptCategories',
        ];
    }
}
