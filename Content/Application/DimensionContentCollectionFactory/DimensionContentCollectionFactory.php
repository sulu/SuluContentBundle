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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentCollectionFactory;

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DimensionContentCollectionFactory implements DimensionContentCollectionFactoryInterface
{
    /**
     * @var iterable<MergerInterface>
     */
    private $mergers;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * @param iterable<MergerInterface> $mergers
     */
    public function __construct(
        iterable $mergers,
        PropertyAccessor $propertyAccessor
    ) {
        $this->mergers = $mergers;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function create(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): DimensionContentCollectionInterface {
        return new DimensionContentCollection(
            $contentRichEntity,
            $dimensionAttributes,
            $this->mergers,
            $this->propertyAccessor
        );
    }
}
