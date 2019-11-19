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

namespace Sulu\Bundle\ContentBundle\TestCases\Content;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;

/**
 * Trait to test your implementation of the TemplateInterface.
 */
trait RoutableTestCaseTrait
{
    abstract protected function getRoutableInstance(DimensionInterface $dimension): RoutableInterface;

    public function testGetLocale(): void
    {
        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');

        $model = $this->getRoutableInstance($dimension->reveal());
        $this->assertSame('en', $model->getLocale());
    }
}
