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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;

interface ContentCopierInterface
{
    /**
     * @param mixed[] $sourceDimensionAttributes
     * @param mixed[] $targetDimensionAttributes
     */
    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentViewInterface;

    /**
     * @param mixed[] $targetDimensionAttributes
     */
    public function copyFromDimensionContentCollection(
        DimensionContentCollectionInterface $dimensionContentCollection,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentViewInterface;

    /**
     * @param mixed[] $targetDimensionAttributes
     */
    public function copyFromContentView(
        ContentViewInterface $sourceContentView,
        ContentRichEntityInterface $targetContentRichENtity,
        array $targetDimensionAttributes
    ): ContentViewInterface;
}
