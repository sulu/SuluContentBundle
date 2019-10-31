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

namespace Sulu\Bundle\ContentBundle\Content\Application;

use Sulu\Bundle\ContentBundle\Content\Application\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;

class ContentDimensionMerger implements ContentDimensionMergerInterface
{
    /**
     * @var iterable<MergerInterface>
     */
    private $mergers = [];

    /**
     * @param iterable<MergerInterface> $mergers
     */
    public function __construct(iterable $mergers)
    {
        $this->mergers = $mergers;
    }

    public function merge(array $contentDimensions): array
    {
        $data = [];

        foreach ($contentDimensions as $contentDimension) {
            $data = $this->mergeContentDimension($contentDimension, $data);
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function mergeContentDimension(ContentDimensionInterface $object, array $data): array
    {
        foreach ($this->mergers as $merger) {
            $data = $merger->merge($object, $data);
        }

        return $data;
    }
}
