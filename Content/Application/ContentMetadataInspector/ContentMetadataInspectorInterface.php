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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentMetadataInspector;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

interface ContentMetadataInspectorInterface
{
    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     *
     * @return class-string<DimensionContentInterface>
     */
    public function getDimensionContentClass(string $contentRichEntityClass): string;

    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     */
    public function getDimensionContentPropertyName(string $contentRichEntityClass): string;
}
