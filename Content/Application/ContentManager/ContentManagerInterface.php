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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentManager;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

interface ContentManagerInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     */
    public function resolve(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): DimensionContentInterface;

    /**
     * @param mixed[] $dimensionAttributes
     * @param mixed[] $data
     */
    public function persist(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes, array $data): DimensionContentCollectionInterface;

    /**
     * @return mixed[]
     */
    public function normalize(DimensionContentInterface $dimensionContent): array;

    /**
     * @param mixed[] $sourceDimensionAttributes
     * @param mixed[] $targetDimensionAttributes
     */
    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): DimensionContentInterface;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function applyTransition(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        string $transitionName
    ): DimensionContentInterface;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function index(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): DimensionContentInterface;

    /**
     * @param mixed $id
     * @param mixed[] $dimensionAttributes
     */
    public function deindex(string $resourceKey, $id, array $dimensionAttributes = []): void;
}
