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

namespace Sulu\Bundle\ContentBundle\Model\Dimension\Exception;

use Sulu\Bundle\ContentBundle\Common\Model\ModelNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionAttribute;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;

class DimensionAttributeNotFoundException extends ModelNotFoundException
{
    public function __construct(DimensionInterface $dimension, string $key, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(DimensionAttribute::class, sprintf('%s#%s', $dimension->getId(), $key), $code, $previous);
    }
}
