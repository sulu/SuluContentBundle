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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

interface ResolverInterface
{
    /**
     * @param mixed[] $viewData
     *
     * @return mixed[]
     */
    public function resolve(object $contentView, array $viewData): array;

    /**
     * @return string[]
     */
    public function getIgnoreAttributes(object $contentView): array;
}
