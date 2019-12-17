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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentCopier;

use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentProjectionNormalizer\ContentProjectionNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

class ContentCopier implements ContentCopierInterface
{
    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var ContentProjectionFactoryInterface
     */
    private $viewFactory;

    /**
     * @var ContentPersisterInterface
     */
    private $contentPersister;

    /**
     * @var ContentProjectionNormalizerInterface
     */
    private $contentProjectionNormalizer;

    public function __construct(
        ContentResolverInterface $contentResolver,
        ContentProjectionFactoryInterface $viewFactory,
        ContentPersisterInterface $contentPersister,
        ContentProjectionNormalizerInterface $contentProjectionNormalizer
    ) {
        $this->contentResolver = $contentResolver;
        $this->viewFactory = $viewFactory;
        $this->contentPersister = $contentPersister;
        $this->contentProjectionNormalizer = $contentProjectionNormalizer;
    }

    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentProjectionInterface {
        $sourceContentProjection = $this->contentResolver->resolve($sourceContentRichEntity, $sourceDimensionAttributes);

        return $this->copyFromContentProjection($sourceContentProjection, $targetContentRichEntity, $targetDimensionAttributes);
    }

    public function copyFromDimensionContentCollection(
        DimensionContentCollectionInterface $dimensionContentCollection,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentProjectionInterface {
        $sourceContentProjection = $this->viewFactory->create($dimensionContentCollection);

        return $this->copyFromContentProjection($sourceContentProjection, $targetContentRichEntity, $targetDimensionAttributes);
    }

    public function copyFromContentProjection(
        ContentProjectionInterface $sourceContentProjection,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentProjectionInterface {
        $data = $this->contentProjectionNormalizer->normalize($sourceContentProjection);

        return $this->contentPersister->persist($targetContentRichEntity, $data, $targetDimensionAttributes);
    }
}
