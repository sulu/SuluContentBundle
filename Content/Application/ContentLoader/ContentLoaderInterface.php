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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentLoader;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

interface ContentLoaderInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     *
     * @return mixed[]
     */
    public function load(ContentInterface $content, array $dimensionAttributes): array;
}
