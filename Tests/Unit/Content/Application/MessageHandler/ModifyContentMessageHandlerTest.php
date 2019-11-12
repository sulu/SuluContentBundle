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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\MessageHandler\ModifyContentMessageHandler;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentDimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Factory\DimensionCollectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionCollection;

class ModifyContentMessageHandlerTest extends TestCase
{
    protected function createModifyContentMessageHandlerInstance(
        DimensionCollectionFactoryInterface $dimensionCollectionFactory,
        ContentDimensionCollectionFactoryInterface $contentDimensionCollectionFactory,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ): ModifyContentMessageHandler {
        return new ModifyContentMessageHandler(
            $dimensionCollectionFactory,
            $contentDimensionCollectionFactory,
            $viewFactory,
            $viewResolver
        );
    }

    protected function createContentInstance(): ContentInterface
    {
        return new class() extends AbstractContent {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimension(string $dimensionId): ContentDimensionInterface
            {
                return new \RuntimeException('Should not be called in a unit test.');
            }
        };
    }

    public function testInvoke(): void
    {
        $content = $this->createContentInstance();
        $attributes = [
            'locale' => 'de',
        ];
        $data = [
            'data' => 'value',
        ];

        $message = new ModifyContentMessage($content, $data, $attributes);

        $dimension1 = new Dimension('123-456', ['locale' => 'de']);
        $dimension2 = new Dimension('456-789', ['locale' => null]);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension2, $dimension1]);

        $dimensionCollectionFactory = $this->prophesize(DimensionCollectionFactoryInterface::class);
        $dimensionCollectionFactory->create($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]);

        $contentDimensionCollectionFactory = $this->prophesize(ContentDimensionCollectionFactoryInterface::class);
        $contentDimensionCollectionFactory->create($content, $dimensionCollection, $data)
            ->willReturn($contentDimensionCollection)
            ->shouldBeCalled();

        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($contentDimensionCollection)->willReturn($contentView->reveal())->shouldBeCalled();

        $viewResolver = $this->prophesize(ApiViewResolverInterface::class);
        $viewResolver->resolve($contentView->reveal())->willReturn(['resolved' => 'data'])->shouldBeCalled();

        $createContentMessageHandler = $this->createModifyContentMessageHandlerInstance(
            $dimensionCollectionFactory->reveal(),
            $contentDimensionCollectionFactory->reveal(),
            $viewFactory->reveal(),
            $viewResolver->reveal()
        );

        $this->assertSame(['resolved' => 'data'], $createContentMessageHandler->__invoke($message));
    }
}
