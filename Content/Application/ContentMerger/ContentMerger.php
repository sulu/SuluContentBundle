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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentMerger;

use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory\Merger\MergerInterface;

class ContentMerger implements ContentMergerInterface
{
    /**
     * @var iterable<MergerInterface>
     */
    private $mergers;

    /**
     * @param iterable<MergerInterface> $mergers
     */
    public function __construct(iterable $mergers)
    {
        $this->mergers = $mergers;
    }

    public function merge(object $targetObject, object $sourceObject): void
    {
        foreach ($this->mergers as $merger) {
            $merger->merge($targetObject, $sourceObject);
        }
    }
}
