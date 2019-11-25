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

interface DimensionInterface
{
    const STAGE_DRAFT = 'draft';
    const STAGE_LIVE = 'live';

    public function getId(): string;

    public function getLocale(): ?string;

    public function getStage(): string;

    /**
     * @return mixed[]
     */
    public function getAttributes(): array;

    /**
     * @return array<string, mixed>
     */
    public static function getDefaultValues(): array;
}
