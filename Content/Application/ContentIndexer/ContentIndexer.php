<?php

declare(strict_types=1);

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentIndexer;

use Massive\Bundle\SearchBundle\Search\QueryHit;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;

class ContentIndexer implements ContentIndexerInterface
{
    /**
     * @var SearchManagerInterface
     */
    private $searchManager;

    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    public function __construct(SearchManagerInterface $searchManager, ContentResolverInterface $contentResolver)
    {
        $this->searchManager = $searchManager;
        $this->contentResolver = $contentResolver;
    }

    public function index(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): void
    {
        $dimensionContent = $this->loadDimensionContent($contentRichEntity, $dimensionAttributes);

        if (null === $dimensionContent) {
            return;
        }

        $this->indexDimensionContent($dimensionContent);
    }

    public function indexDimensionContent(DimensionContentInterface $dimensionContent): void
    {
        if (!$dimensionContent->isMerged()) {
            return;
        }

        $this->searchManager->index($dimensionContent);
    }

    public function deindex(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): void
    {
        $dimensionContent = $this->loadDimensionContent($contentRichEntity, $dimensionAttributes);

        if (null === $dimensionContent) {
            return;
        }

        $this->deindexDimensionContent($dimensionContent);
    }

    public function deindexDimensionContent(DimensionContentInterface $dimensionContent): void
    {
        if (!$dimensionContent->isMerged()) {
            return;
        }

        $this->searchManager->deindex($dimensionContent);
    }

    public function delete(string $resourceKey, $id): void
    {
        $searchResult = $this->searchManager
            ->createSearch(sprintf('__id:"%s"', $id))
            ->indexes($this->getIndexes($resourceKey))
            ->execute();

        /** @var QueryHit $hit */
        foreach ($searchResult as $hit) {
            $this->searchManager->deindex($hit->getDocument());
        }
    }

    /**
     * @param mixed[] $dimensionAttributes
     */
    private function loadDimensionContent(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes
    ): ?DimensionContentInterface {
        $locale = $dimensionAttributes['locale'] ?? null;
        $stage = $dimensionAttributes['stage'] ?? null;

        if (null === $locale || null === $stage) {
            return null;
        }

        try {
            $dimensionContent = $this->contentResolver->resolve($contentRichEntity, $dimensionAttributes);
        } catch (ContentNotFoundException $e) {
            return null;
        }

        if ($locale !== $dimensionContent->getDimension()->getLocale()
            || $stage !== $dimensionContent->getDimension()->getStage()) {
            return null;
        }

        return $dimensionContent;
    }

    /**
     * @return string[]
     */
    private function getIndexes(string $resourceKey): array
    {
        return \array_filter(
            $this->searchManager->getIndexNames(),
            function($index) use ($resourceKey) {
                return $resourceKey === $index || $resourceKey . '_published' === $index;
            }
        );
    }
}
