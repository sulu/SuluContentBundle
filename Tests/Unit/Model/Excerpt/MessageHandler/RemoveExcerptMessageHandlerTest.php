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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Excerpt\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\RemoveExcerptMessage;
use Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler\RemoveExcerptMessageHandler;

class RemoveExcerptMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_excerpts';

    public function testInvoke(): void
    {
        $excerptDimensionRepository = $this->prophesize(ExcerptDimensionRepositoryInterface::class);

        $handler = new RemoveExcerptMessageHandler($excerptDimensionRepository->reveal());

        $message = $this->prophesize(RemoveExcerptMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);

        $excerptDimension1 = $this->prophesize(ExcerptDimensionInterface::class);
        $excerptDimension2 = $this->prophesize(ExcerptDimensionInterface::class);

        $excerptDimensionRepository->findByResource(self::RESOURCE_KEY, 'resource-1')
            ->shouldBeCalled()->willReturn([$excerptDimension1->reveal(), $excerptDimension2->reveal()]);

        $excerptDimensionRepository->removeDimension($excerptDimension1->reveal())->shouldBeCalled();
        $excerptDimensionRepository->removeDimension($excerptDimension2->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
