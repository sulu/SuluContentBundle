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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ShadowInterface;

class ShadowDataMapper implements DataMapperInterface
{
    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data
    ): void {
        if (!$localizedDimensionContent instanceof ShadowInterface) {
            return;
        }

        if (\array_key_exists('shadowOn', $data) || \array_key_exists('shadowLocale', $data)) {
            /** @var bool $shadowOn */
            $shadowOn = $data['shadowOn'] ?? false;
            /** @var string|null $shadowLocale */
            $shadowLocale = $data['shadowLocale'] ?? null;

            $localizedDimensionContent->setShadowLocale(
                $shadowOn
                    ? $shadowLocale
                    : null
            );
        }
    }
}
