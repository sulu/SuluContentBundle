<?php

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentIndexer;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

interface ContentIndexerInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     */
    public function index(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): void;

    public function indexDimensionContent(DimensionContentInterface $dimensionContent): void;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function deindex(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): void;

    public function deindexDimensionContent(DimensionContentInterface $dimensionContent): void;

    /**
     * @param mixed $id
     */
    public function delete(string $resourceKey, $id): void;
}
