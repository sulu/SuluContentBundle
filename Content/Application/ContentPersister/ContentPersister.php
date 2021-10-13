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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentPersister;

use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\DimensionContentCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

class ContentPersister implements ContentPersisterInterface
{
    /**
     * @var DimensionContentCollectionFactoryInterface
     */
    private $dimensionContentCollectionFactory;

    /**
     * @var ContentDataMapperInterface
     */
    private $contentDataMapper;

    public function __construct(
        DimensionContentCollectionFactoryInterface $dimensionContentCollectionFactory,
        ContentDataMapperInterface $contentDataMapper
    ) {
        $this->dimensionContentCollectionFactory = $dimensionContentCollectionFactory;
        $this->contentDataMapper = $contentDataMapper;
    }

    public function persist(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes, array $data): DimensionContentCollectionInterface
    {
        $dimensionContentCollection = $this->dimensionContentCollectionFactory->create(
            $contentRichEntity,
            $dimensionAttributes
        );

        $this->contentDataMapper->map($dimensionContentCollection, $dimensionAttributes, $data);

        return $dimensionContentCollection;
    }
}
