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

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentCopier implements ContentCopierInterface
{
    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var ContentMergerInterface
     */
    private $contentMerger;

    /**
     * @var ContentPersisterInterface
     */
    private $contentPersister;

    /**
     * @var ContentNormalizerInterface
     */
    private $contentNormalizer;

    public function __construct(
        ContentResolverInterface $contentResolver,
        ContentMergerInterface $contentMerger,
        ContentPersisterInterface $contentPersister,
        ContentNormalizerInterface $contentNormalizer
    ) {
        $this->contentResolver = $contentResolver;
        $this->contentMerger = $contentMerger;
        $this->contentPersister = $contentPersister;
        $this->contentNormalizer = $contentNormalizer;
    }

    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes,
        array $data = [],
        array $ignoredAttributes = []
    ): DimensionContentInterface {
        $sourceDimensionContent = $this->contentResolver->resolve($sourceContentRichEntity, $sourceDimensionAttributes);

        return $this->copyFromDimensionContent($sourceDimensionContent, $targetContentRichEntity, $targetDimensionAttributes, $data, $ignoredAttributes);
    }

    public function copyFromDimensionContentCollection(
        DimensionContentCollectionInterface $dimensionContentCollection,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes,
        array $data = [],
        array $ignoredAttributes = []
    ): DimensionContentInterface {
        $sourceDimensionContent = $this->contentMerger->merge($dimensionContentCollection);

        return $this->copyFromDimensionContent($sourceDimensionContent, $targetContentRichEntity, $targetDimensionAttributes, $data, $ignoredAttributes);
    }

    public function copyFromDimensionContent(
        DimensionContentInterface $dimensionContent,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes,
        array $data = [],
        array $ignoredAttributes = []
    ): DimensionContentInterface {
        $data = \array_replace($this->contentNormalizer->normalize($dimensionContent), $data);

        foreach ($ignoredAttributes as $ignoredAttribute) {
            unset($data[$ignoredAttribute]);
        }

        return $this->contentPersister->persist($targetContentRichEntity, $data, $targetDimensionAttributes);
    }
}
