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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentAssociationMapper;

interface ContentAssociationMapperInterface
{
    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     *
     * @return class-string<DimensionContentInterface>
     */
    public function getDimensionContentClass(string $contentRichEntityClass): string;
}
