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

namespace Sulu\Bundle\ContentBundle\Content\Application\Merger;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;

abstract class AbstractMerger implements MergerInterface
{
    abstract protected function supports(ContentDimensionInterface $object): bool;

    abstract protected function getKey(ContentDimensionInterface $object): string;

    /**
     * @param object $object
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    abstract protected function mergeData($object, array $data): array;

    public function merge(ContentDimensionInterface $object, array $data): array
    {
        if (!$this->supports($object)) {
            return $data;
        }

        $key = $this->getKey($object);

        $data[$key] = $this->mergeData($object, $data[$key] ?? []);

        return $data;
    }
}
