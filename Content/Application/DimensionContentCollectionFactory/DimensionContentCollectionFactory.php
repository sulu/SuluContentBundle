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
use Sulu\Bundle\ContentBundle\Content\Application\ContentMetadataInspector\ContentMetadataInspectorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class DimensionContentCollectionFactory implements DimensionContentCollectionFactoryInterface
{
    /**
     * @var ContentMetadataInspectorInterface
     */
    private $contentMetadataInspector;

    /**
     * @var iterable<MergerInterface>
     */
    private $mergers;

    /**
     * @var PropertyAccessor
     */
    private $propertyAccessor;

    public function __construct(
        iterable $mergers,
        ContentMetadataInspectorInterface $contentMetadataInspector,
        PropertyAccessor $propertyAccessor
    ) {
        $this->mergers = $mergers;
        $this->contentMetadataInspector = $contentMetadataInspector;
        $this->propertyAccessor = $propertyAccessor;
    }

    public function create(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): DimensionContentCollectionInterface {
        return new DimensionContentCollection(
            $contentRichEntity,
            $dimensionAttributes,
            $this->contentMetadataInspector->getDimensionContentClass(\get_class($this)),
            $this->mergers,
            $this->propertyAccessor
        );
    }
}
