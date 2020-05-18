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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableTrait;

class RoutableTraitTest extends TestCase
{
    protected function getRoutableInstance(
        ContentRichEntityInterface $contentRichEntity,
        DimensionInterface $dimension
    ): RoutableInterface {
        return new class($contentRichEntity, $dimension) implements RoutableInterface {
            use RoutableTrait;

            /**
             * @var DimensionInterface
             */
            private $dimension;

            /**
             * @var ContentRichEntityInterface
             */
            private $contentRichEntity;

            public function __construct(ContentRichEntityInterface $contentRichEntity, DimensionInterface $dimension)
            {
                $this->contentRichEntity = $contentRichEntity;
                $this->dimension = $dimension;
            }

            public static function getContentClass(): string
            {
                return self::class;
            }

            public function getDimension(): DimensionInterface
            {
                return $this->dimension;
            }

            public function getContentRichEntity(): ContentRichEntityInterface
            {
                return $this->contentRichEntity;
            }
        };
    }

    public function testGetLocale(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $model = $this->getRoutableInstance($contentRichEntity->reveal(), $dimension->reveal());
        $this->assertSame('en', $model->getLocale());
    }

    public function getContentId(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('content-id-123');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $model = $this->getRoutableInstance($contentRichEntity->reveal(), $dimension->reveal());
        $this->assertSame('content-id-123', $model->getContentId());
    }
}
