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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateNormalizeEnhancer implements NormalizeEnhancerInterface
{
    public function enhance(object $object, array $normalizeData): array
    {
        if (!$object instanceof TemplateInterface) {
            return $normalizeData;
        }

        $normalizeData = array_merge($normalizeData['templateData'], $normalizeData);
        unset($normalizeData['templateData']);

        $normalizeData['template'] = $normalizeData['templateKey'];
        unset($normalizeData['templateKey']);

        return $normalizeData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        if (!$object instanceof TemplateInterface) {
            return [];
        }

        return [
            'templateType',
        ];
    }
}
