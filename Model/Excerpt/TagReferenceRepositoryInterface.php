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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt;

use Sulu\Bundle\TagBundle\Tag\TagInterface;

interface TagReferenceRepositoryInterface
{
    public function create(
        ExcerptDimensionInterface $excerptDimension,
        TagInterface $tag
    ): TagReferenceInterface;

    public function remove(TagReferenceInterface $tagReference): void;
}
