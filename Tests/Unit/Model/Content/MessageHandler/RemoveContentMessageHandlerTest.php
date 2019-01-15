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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Content\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\RemoveContentMessage;
use Sulu\Bundle\ContentBundle\Model\Content\MessageHandler\RemoveContentMessageHandler;

class RemoveContentMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_contents';

    public function testInvoke(): void
    {
        $contentDimensionRepository = $this->prophesize(ContentDimensionRepositoryInterface::class);

        $handler = new RemoveContentMessageHandler($contentDimensionRepository->reveal());

        $message = $this->prophesize(RemoveContentMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);

        $contentDimension1 = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension2 = $this->prophesize(ContentDimensionInterface::class);

        $contentDimensionRepository->findByResource(self::RESOURCE_KEY, 'resource-1')
            ->shouldBeCalled()->willReturn([$contentDimension1->reveal(), $contentDimension2->reveal()]);

        $contentDimensionRepository->removeDimension($contentDimension1->reveal())->shouldBeCalled();
        $contentDimensionRepository->removeDimension($contentDimension2->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
