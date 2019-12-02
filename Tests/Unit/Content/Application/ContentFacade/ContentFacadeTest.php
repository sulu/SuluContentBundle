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
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacade;
use Sulu\Bundle\ContentBundle\Content\Application\ContentFacade\ContentFacadeInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentFacadeTest extends TestCase
{
    protected function createContentFacadeInstance(
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver
    ): ContentFacadeInterface {
        return new ContentFacade($contentLoader, $contentPersister, $contentResolver);
    }

    public function testLoad(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);
        $content = $this->prophesize(ContentInterface::class);
        $dimensionAttributes = ['locale' => 'de', 'stage' => 'draft'];

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $contentLoader->load($content->reveal(), $dimensionAttributes)
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();
        $contentFacade->load($content->reveal(), $dimensionAttributes);
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

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $contentPersister->persist($content->reveal(), $data, $dimensionAttributes)
            ->willReturn($contentView->reveal())
            ->shouldBeCalled();
        $contentFacade->persist($content->reveal(), $data, $dimensionAttributes);
    }

    public function testResolve(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);

        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $contentPersister = $this->prophesize(ContentPersisterInterface::class);
        $contentResolver = $this->prophesize(ApiViewResolverInterface::class);

        $contentFacade = $this->createContentFacadeInstance(
            $contentLoader->reveal(),
            $contentPersister->reveal(),
            $contentResolver->reveal()
        );

        $contentResolver->resolve($contentView->reveal())
            ->willReturn(['resolved' => 'data'])
            ->shouldBeCalled();
        $contentFacade->resolve($contentView->reveal());
    }
}
