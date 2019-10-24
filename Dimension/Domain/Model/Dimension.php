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

namespace Sulu\Bundle\ContentBundle\Dimension\Domain\Model;

use Ramsey\Uuid\Uuid;

class Dimension implements DimensionInterface
{
    /**
     * @var int
     */
    private $no;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var bool
     */
    private $published = false;

    public function __construct(?string $id = null, ?string $locale = null, bool $published = false)
    {
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->locale = $locale;
        $this->published = $published;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getPublished(): bool
    {
        return $this->published;
    }
}
