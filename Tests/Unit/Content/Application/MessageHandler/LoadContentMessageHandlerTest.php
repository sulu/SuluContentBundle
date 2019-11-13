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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\MessageHandler\LoadContentMessageHandler;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class LoadContentMessageHandlerTest extends TestCase
{
    protected function createLoadContentMessageHandlerInstance(
        DimensionRepositoryInterface $dimensionRepository,
        ViewFactoryInterface $viewFactory,
        ApiViewResolverInterface $viewResolver
    ): LoadContentMessageHandler {
        return new LoadContentMessageHandler(
            $dimensionRepository,
            $viewFactory,
            $viewResolver
        );
    }

    /**
     * @param ContentDimensionInterface[] $dimensions
     */
    protected function createContentInstance(array $dimensions): ContentInterface
    {
        $content = new class() extends AbstractContent {
            public static function getResourceKey(): string
            {
                return 'example';
            }

            public function createDimension(string $dimensionId): ContentDimensionInterface
            {
                throw new \RuntimeException('Should not be called in a unit test.');
            }
        };

        foreach ($dimensions as $dimension) {
            $content->addDimension($dimension);
        }

        return $content;
    }

    public function testInvoke(): void
    {
        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension1->getDimensionId()->willReturn('123-456');
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2->getDimensionId()->willReturn('456-789');

        $content = $this->createContentInstance([
            $contentDimension2->reveal(),
            $contentDimension1->reveal(),
        ]);

        $attributes = [
            'locale' => 'de',
        ];

        $message = new LoadContentMessage($content, $attributes);

        $dimension1 = new Dimension('123-456', ['locale' => null]);
        $dimension2 = new Dimension('456-789', ['locale' => 'de']);
        $dimensionCollection = new DimensionCollection($attributes, [$dimension1, $dimension2]);

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findByAttributes($attributes)->willReturn($dimensionCollection)->shouldBeCalled();

        $contentDimensionCollection = new ContentDimensionCollection([
            $contentDimension1->reveal(),
            $contentDimension2->reveal(),
        ]);

        $contentView = $this->prophesize(ContentViewInterface::class);
        $viewFactory = $this->prophesize(ViewFactoryInterface::class);
        $viewFactory->create($contentDimensionCollection)->willReturn($contentView->reveal())->shouldBeCalled();

        $viewResolver = $this->prophesize(ApiViewResolverInterface::class);
        $viewResolver->resolve($contentView->reveal())->willReturn(['resolved' => 'data'])->shouldBeCalled();

        $createContentMessageHandler = $this->createLoadContentMessageHandlerInstance(
            $dimensionRepository->reveal(),
            $viewFactory->reveal(),
            $viewResolver->reveal()
        );

        $this->assertSame(['resolved' => 'data'], $createContentMessageHandler->__invoke($message));
    }
}
