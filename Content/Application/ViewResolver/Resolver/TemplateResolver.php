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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateResolver implements ResolverInterface
{
    public function resolve(object $contentProjection, array $viewData): array
    {
        if (!$contentProjection instanceof TemplateInterface) {
            return $viewData;
        }

        $viewData = array_merge($viewData, $viewData['templateData']);
        unset($viewData['templateData']);

        $viewData['template'] = $viewData['templateKey'];
        unset($viewData['templateKey']);

        return $viewData;
    }

    public function getIgnoredAttributes(object $contentProjection): array
    {
        return [
            'templateType',
        ];
    }
}
