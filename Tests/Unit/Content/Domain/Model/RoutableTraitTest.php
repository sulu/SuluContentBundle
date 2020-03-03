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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableTrait;

class RoutableTraitTest extends TestCase
{
    protected function getRoutableInstance(DimensionInterface $dimension): RoutableInterface
    {
        return new class($dimension) implements RoutableInterface {
            use RoutableTrait;

            /**
             * @var DimensionInterface
             */
            private $dimension;

            public function __construct(DimensionInterface $dimension)
            {
                $this->dimension = $dimension;
            }

            public static function getContentClass(): string
            {
                return self::class;
            }

            public function getContentId()
            {
                return 1;
            }

            public function getDimension(): DimensionInterface
            {
                return $this->dimension;
            }
        };
    }

    public function testGetLocale(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $model = $this->getRoutableInstance($dimension->reveal());
        $this->assertSame('en', $model->getLocale());
    }
}
