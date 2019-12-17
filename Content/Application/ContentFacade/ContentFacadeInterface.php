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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentFacade;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

interface ContentFacadeInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     */
    public function resolve(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): ContentProjectionInterface;

    /**
     * @param mixed[] $data
     * @param mixed[] $dimensionAttributes
     */
    public function persist(ContentRichEntityInterface $contentRichEntity, array $data, array $dimensionAttributes): ContentProjectionInterface;

    /**
     * @return mixed[]
     */
    public function normalize(ContentProjectionInterface $contentProjection): array;

    /**
     * @param mixed[] $sourceDimensionAttributes
     * @param mixed[] $targetDimensionAttributes
     */
    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentProjectionInterface;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function applyTransition(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        string $transitionName
    ): ContentProjectionInterface;
}
