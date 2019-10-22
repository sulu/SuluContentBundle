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

use Ramsey\Uuid\Uuid;

class Dimension
{
    /**
     * @var int
     */
    private $no;

    /**
     * @var string
     */
    private $id;

    public function __construct(?string $id = null)
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
    }

    public function getId(): string
    {
        return $this->id;
    }
}
