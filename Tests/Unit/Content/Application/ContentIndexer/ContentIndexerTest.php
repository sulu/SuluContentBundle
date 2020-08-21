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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentIndexer;

use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\QueryHit;
use Massive\Bundle\SearchBundle\Search\SearchManagerInterface;
use Massive\Bundle\SearchBundle\Search\SearchQueryBuilder;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentIndexer\ContentIndexer;
use Sulu\Bundle\ContentBundle\Content\Application\ContentIndexer\ContentIndexerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class ContentIndexerTest extends TestCase
{
    protected function createContentIndexerInstance(
        SearchManagerInterface $searchManager,
        ContentResolverInterface $contentResolver
    ): ContentIndexerInterface {
        return new ContentIndexer($searchManager, $contentResolver);
    }

    public function testIndex(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(true);
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($dimensionContent->reveal());
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $searchManager->index($dimensionContent->reveal())->shouldBeCalled();

        $this->assertSame(
            $dimensionContent->reveal(),
            $contentIndexer->index($contentRichEntity->reveal(), $dimensionAttributes)
        );
    }

    public function testIndexContentNotFound(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willThrow(ContentNotFoundException::class);

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->index($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testIndexWrongLocale(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($dimensionContent->reveal());
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->index($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testIndexInvalidDimensionAttributes(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de'];

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->index($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testDeindex(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(true);
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($dimensionContent->reveal());
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $searchManager->deindex($dimensionContent->reveal())->shouldBeCalled();

        $this->assertSame(
            $dimensionContent->reveal(),
            $contentIndexer->deindex($contentRichEntity->reveal(), $dimensionAttributes)
        );
    }

    public function testDeindexContentNotFound(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willThrow(ContentNotFoundException::class);

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->deindex($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testDeindexWrongLocale(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'en', 'stage' => DimensionInterface::STAGE_DRAFT];
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $contentResolver->resolve($contentRichEntity->reveal(), $dimensionAttributes)
            ->willReturn($dimensionContent->reveal());
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn(null);
        $dimension->getStage()->willReturn(DimensionInterface::STAGE_DRAFT);
        $dimensionContent->getDimension()->willReturn($dimension->reveal());

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->deindex($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testDeindexInvalidDimensionAttributes(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $dimensionAttributes = ['locale' => 'de'];

        $this->expectException(ContentNotFoundException::class);

        $contentIndexer->deindex($contentRichEntity->reveal(), $dimensionAttributes);
    }

    public function testIndexDimensionContent(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(true);

        $searchManager->index($dimensionContent->reveal())->shouldBeCalled();

        $contentIndexer->indexDimensionContent($dimensionContent->reveal());
    }

    public function testIndexDimensionContentNotMerged(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(false);

        $searchManager->index($dimensionContent->reveal())->shouldNotBeCalled();

        $contentIndexer->indexDimensionContent($dimensionContent->reveal());
    }

    public function testDeindexDimensionContent(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(true);

        $searchManager->deindex($dimensionContent->reveal())->shouldBeCalled();

        $contentIndexer->deindexDimensionContent($dimensionContent->reveal());
    }

    public function testDeindexDimensionContentNotMerged(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->isMerged()->willReturn(false);

        $searchManager->deindex($dimensionContent->reveal())->shouldNotBeCalled();

        $contentIndexer->deindexDimensionContent($dimensionContent->reveal());
    }

    public function testDelete(): void
    {
        $searchManager = $this->prophesize(SearchManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);

        $contentIndexer = $this->createContentIndexerInstance(
            $searchManager->reveal(),
            $contentResolver->reveal()
        );

        $resourceKey = 'examples';
        $resourceId = '123';

        $searchManager->getIndexNames()->willReturn([
            'massive_page_example-en-i18n' => 'page_example',
            'massive_page_example_published-en-i18n' => 'page_example_published',
            'massive_examples-en-i18n' => 'examples',
            'massive_examples_published-en-i18n' => 'examples_published',
            'massive_contact' => 'contact',
        ]);

        $document1 = $this->prophesize(Document::class);
        $queryHit1 = $this->prophesize(QueryHit::class);
        $queryHit1->getDocument()->willReturn($document1->reveal());
        $document2 = $this->prophesize(Document::class);
        $queryHit2 = $this->prophesize(QueryHit::class);
        $queryHit2->getDocument()->willReturn($document2->reveal());

        $searchQueryBuilder = $this->prophesize(SearchQueryBuilder::class);
        $searchQueryBuilder->indexes([
            'massive_examples-en-i18n' => 'examples',
            'massive_examples_published-en-i18n' => 'examples_published',
        ])->willReturn($searchQueryBuilder->reveal());
        $searchQueryBuilder->execute()->willReturn([$queryHit1->reveal(), $queryHit2->reveal()]);
        $searchManager->createSearch('__id:"123"')->willReturn($searchQueryBuilder->reveal());

        $searchManager->deindex($document1->reveal())->shouldBeCalled();
        $searchManager->deindex($document2->reveal())->shouldBeCalled();

        $contentIndexer->delete($resourceKey, $resourceId);
    }
}
