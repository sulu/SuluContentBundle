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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\Exception;

use Sulu\Bundle\ContentBundle\Common\Model\ModelNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimension;

class ExcerptNotFoundException extends ModelNotFoundException
{
    public function __construct(string $resourceKey, string $resourceId, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(ExcerptDimension::class, sprintf('%s#%s', $resourceKey, $resourceId), $code, $previous);
    }
}
