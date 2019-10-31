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

namespace Sulu\Bundle\ContentBundle\Content\Application\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;

class SeoMerger extends AbstractMerger
{
    protected function supports(ContentDimensionInterface $object): bool
    {
        return $object instanceof SeoInterface;
    }

    protected function getKey(ContentDimensionInterface $object): string
    {
        return 'seo';
    }

    /**
     * @param SeoInterface $object
     */
    protected function mergeData($object, array $data): array
    {
        foreach ($object->seoToArray() as $key => $value) {
            if (!array_key_exists($key, $data) || !empty($value)) {
                $data[$key] = $value;
            }
        }

        return $data;
    }
}
