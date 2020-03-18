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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionFactory;

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentProjectionFactory implements ContentProjectionFactoryInterface
{
    /**
     * @var ContentMergerInterface
     */
    private $contentMerger;

    public function __construct(ContentMergerInterface $contentMerger)
    {
        $this->contentMerger = $contentMerger;
    }

    public function create(DimensionContentCollectionInterface $dimensionContentCollection): ContentProjectionInterface
    {
        if (!$dimensionContentCollection->count()) {
            throw new \RuntimeException('Expected at least one dimensionContent given.');
        }

        /** @var DimensionContentInterface[] $dimensionContentCollectionArray */
        $dimensionContentCollectionArray = iterator_to_array($dimensionContentCollection);
        $lastKey = \count($dimensionContentCollectionArray) - 1;

        $contentProjection = $dimensionContentCollectionArray[$lastKey]->createProjectionInstance();

        foreach ($dimensionContentCollection as $dimensionContent) {
            $this->contentMerger->merge($contentProjection, $dimensionContent);
        }

        return $contentProjection;
    }
}
