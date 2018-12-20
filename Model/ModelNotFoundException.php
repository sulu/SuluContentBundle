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

namespace Sulu\Bundle\ContentBundle\Model;

class ModelNotFoundException extends \Exception
{
    /**
     * @var string
     */
    private $identifier;

    public function __construct(string $model, string $identifier, $code = 0, \Throwable $previous = null)
    {
        $message = sprintf('Model "%s" with identifier "%s" not found', $model, $identifier);
        parent::__construct($message, $code, $previous);
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
