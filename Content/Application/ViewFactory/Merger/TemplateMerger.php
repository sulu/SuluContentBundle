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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

class TemplateMerger implements MergerInterface
{
    public function merge(object $contentView, object $dimensionContent): void
    {
        if (!$contentView instanceof TemplateInterface) {
            return;
        }

        if (!$dimensionContent instanceof TemplateInterface) {
            return;
        }

        if ($templateKey = $dimensionContent->getTemplateKey()) {
            $contentView->setTemplateKey($templateKey);
        }

        $contentView->setTemplateData(array_merge(
            $contentView->getTemplateData(),
            $dimensionContent->getTemplateData()
        ));
    }
}
