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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewFactory;

use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ViewFactory implements ViewFactoryInterface
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

    public function create(ContentDimensionCollectionInterface $contentDimensionCollection): ContentViewInterface
    {
        if (!$contentDimensionCollection->count()) {
            throw new \RuntimeException('Expected at least one contentDimension given.');
        }

        /** @var ContentDimensionInterface[] $contentDimensionCollectionArray */
        $contentDimensionCollectionArray = iterator_to_array($contentDimensionCollection);
        $lastKey = \count($contentDimensionCollectionArray) - 1;

        $contentView = $contentDimensionCollectionArray[$lastKey]->createViewInstance();

        foreach ($contentDimensionCollection as $contentDimension) {
            /** @var MergerInterface $merger */
            foreach ($this->mergers as $merger) {
                $merger->merge($contentView, $contentDimension);
            }
        }

        return $contentView;
    }
}
