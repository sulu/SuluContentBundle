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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Normalizer;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;

class ContentProjectionNormalizer implements NormalizerInterface
{
    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof ContentProjectionInterface) {
            return [];
        }

        return ['id'];
    }

    public function enhance(object $object, array $normalizedData): array
    {
        if (!$object instanceof ContentProjectionInterface) {
            return $normalizedData;
        }

        // as a content-projection is a resolved entity, we want to set the entity-id to the returned data
        $normalizedData['id'] = $normalizedData['contentId'];
        unset($normalizedData['contentId']);

        return $normalizedData;
    }
}
