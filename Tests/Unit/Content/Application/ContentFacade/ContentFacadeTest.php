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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentFacade;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacade;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacadeInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentFacadeTest extends TestCase
{
    protected function createContentFacadeInstance(
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver,
        ContentCopierInterface $contentCopier,
        ContentWorkflowInterface $contentWorkflow
    ): ContentFacadeInterface {
        return new ContentFacade($contentLoader, $contentPersister, $contentResolver, $contentCopier, $contentWorkflow);
    }

    public function testLoad(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);
        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentLoader->load($content->reveal(), $dimensionAttributes)
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentView->reveal(),
            $contentFacade->load($content->reveal(), $dimensionAttributes)
        );
    }

    public function testPersist(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);
        $content = $this->prophesize(ContentInterface::class);
        $data = ['data' => 'value'];
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentPersister->persist($content->reveal(), $data, $dimensionAttributes)
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentView->reveal(),
            $contentFacade->persist($content->reveal(), $data, $dimensionAttributes)
        );
    }

    public function testResolve(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentResolver->resolve($contentView->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();

        $this->assertSame(
            ['resolved' => 'data'],
            $contentFacade->resolve($contentView->reveal())
        );
    }

    public function testCopy(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);

        $sourceContent = $this->prophesize(ContentInterface::class);
        $sourceDimensionAttributes = ['locale' => 'en'];
        $targetContent = $this->prophesize(ContentInterface::class);
        $targetDimensionAttributes = ['locale' => 'de'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentCopier->copy(
            $sourceContent->reveal(),
            $sourceDimensionAttributes,
            $targetContent->reveal(),
            $targetDimensionAttributes
        )
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentView->reveal(),
            $contentFacade->copy(
                $sourceContent->reveal(),
                $sourceDimensionAttributes,
                $targetContent->reveal(),
                $targetDimensionAttributes
            )
        );
    }

    public function testTransition(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);

        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'en'];
        $transitionName = 'review';

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);
        $contentCopier = $this->prophesize(ContentCopierInterface::class);
        $contentWorkflow = $this->prophesize(ContentWorkflowInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal(),
            $contentCopier->reveal(),
            $contentWorkflow->reveal()
        );

        $contentWorkflow->apply(
            $content->reveal(),
            $dimensionAttributes,
            $transitionName
        )
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();

        $this->assertSame(
            $contentView->reveal(),
            $contentFacade->applyTransition(
                $content->reveal(),
                $dimensionAttributes,
                $transitionName
            )
        );
    }
}
