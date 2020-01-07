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

namespace Sulu\Bundle\ContentBundle\Resolver;

use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;

interface ContentViewResolverInterface
{
    public function resolve(ContentViewInterface $contentView): array;
}
