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

namespace Sulu\Bundle\ContentBundle\Model\DimensionIdentifier;

interface DimensionIdentifierInterface
{
    const ATTRIBUTE_KEY_STAGE = 'stage';
    const ATTRIBUTE_VALUE_DRAFT = 'draft';
    const ATTRIBUTE_VALUE_LIVE = 'live';

    const ATTRIBUTE_KEY_LOCALE = 'locale';

    public function getId(): string;

    public function getAttributeCount(): int;

    public function getAttributes(): array;

    public function getAttributeValue(string $key): string;

    public function hasAttribute(string $key): bool;
}
