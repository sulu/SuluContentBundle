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

namespace Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\Exception;

use Sulu\Bundle\ContentBundle\Common\Model\ModelNotFoundException;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierAttributeInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;

class DimensionIdentifierAttributeNotFoundException extends ModelNotFoundException
{
    public static function createForDimensionAndKey(DimensionIdentifierInterface $dimension, string $key): self
    {
        return new self(['dimension' => $dimension->getId(), 'key' => $key]);
    }

    public function __construct(array $criteria, $code = 0, \Throwable $previous = null)
    {
        parent::__construct(DimensionIdentifierAttributeInterface::class, $criteria, $code, $previous);
    }
}
