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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentPersister;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

interface ContentPersisterInterface
{
    /**
     * @param mixed[] $data
     * @param mixed[] $dimensionAttributes
     *
     * @return mixed[]
     */
    public function persist(ContentInterface $content, array $data, array $dimensionAttributes): array;
}
