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

namespace Sulu\Bundle\ContentBundle\Model\Content\Exception;

use Sulu\Bundle\ContentBundle\Common\Model\ModelNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimension;

class ContentNotFoundException extends ModelNotFoundException
{
    public function __construct(array $criteria, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(ContentDimension::class, $criteria, $code, $previous);
    }
}
