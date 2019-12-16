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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateMerger implements MergerInterface
{
    public function merge(object $contentProjection, object $dimensionContent): void
    {
        if (!$contentProjection instanceof TemplateInterface) {
            return;
        }

        if (!$dimensionContent instanceof TemplateInterface) {
            return;
        }

        if ($templateKey = $dimensionContent->getTemplateKey()) {
            $contentProjection->setTemplateKey($templateKey);
        }

        $contentProjection->setTemplateData(array_merge(
            $contentProjection->getTemplateData(),
            $dimensionContent->getTemplateData()
        ));
    }
}
