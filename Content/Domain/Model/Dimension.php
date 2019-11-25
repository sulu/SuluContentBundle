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

namespace Sulu\Bundle\ContentBundle\Content\Domain\Model;

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
     * @var string
     */
    private $stage;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        ?string $id = null,
        array $attributes = []
    ) {
        $this->id = $id ?: Uuid::uuid4()->toString();
        $attributes = array_merge(static::getDefaultValues(), $attributes);
        $this->locale = $attributes['locale'];
        $this->stage = $attributes['stage'];
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getStage(): string
    {
        return $this->stage;
    }

    public function getAttributes(): array
    {
        return [
            'locale' => $this->locale,
            'stage' => $this->stage,
        ];
    }

    public static function getDefaultValues(): array
    {
        return [
            'locale' => null,
            'stage' => DimensionInterface::STAGE_DRAFT,
        ];
    }
}
