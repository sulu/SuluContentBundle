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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateNormalizerHelper implements NormalizerHelperInterface
{
    public function normalize(object $object, array $viewData): array
    {
        if (!$object instanceof TemplateInterface) {
            return $viewData;
        }

        $viewData = array_merge($viewData, $viewData['templateData']);
        unset($viewData['templateData']);

        $viewData['template'] = $viewData['templateKey'];
        unset($viewData['templateKey']);

        return $viewData;
    }

    public function getIgnoredAttributes(object $object): array
    {
        return [
            'templateType',
        ];
    }
}
