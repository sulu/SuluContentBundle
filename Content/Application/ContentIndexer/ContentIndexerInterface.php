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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentIndexer;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

interface ContentIndexerInterface
{
    /**
     * @param array{
     *    locale?: string,
     *    stage?: string|null,
     * } $dimensionAttributes
     */
    public function index(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): DimensionContentInterface;

    public function indexDimensionContent(DimensionContentInterface $dimensionContent): void;

    /**
     * @param int|string $id
     * @param array{
     *    locale?: string,
     *    stage?: string|null,
     * } $dimensionAttributes
     */
    public function deindex(string $resourceKey, $id, array $dimensionAttributes = []): void;

    public function deindexDimensionContent(DimensionContentInterface $dimensionContent): void;
}
