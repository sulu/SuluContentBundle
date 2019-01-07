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

namespace Sulu\Bundle\ContentBundle\Common\Model;

class MissingResultException extends \Exception
{
    /**
     * @var string
     */
    private $method;

    public function __construct(string $method)
    {
        parent::__construct(sprintf('Result is missing for method "%s"', $method));

        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
