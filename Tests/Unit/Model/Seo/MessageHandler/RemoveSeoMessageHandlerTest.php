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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\MessageHandler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\RemoveSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler\RemoveSeoMessageHandler;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class RemoveSeoMessageHandlerTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testInvoke(): void
    {
        $seoDimensionRepository = $this->prophesize(SeoDimensionRepositoryInterface::class);

        $handler = new RemoveSeoMessageHandler($seoDimensionRepository->reveal());

        $message = $this->prophesize(RemoveSeoMessage::class);
        $message->getResourceId()->shouldBeCalled()->willReturn('resource-1');
        $message->getResourceKey()->shouldBeCalled()->willReturn(self::RESOURCE_KEY);

        $seoDimension1 = $this->prophesize(SeoDimensionInterface::class);
        $seoDimension2 = $this->prophesize(SeoDimensionInterface::class);

        $seoDimensionRepository->findByResource(self::RESOURCE_KEY, 'resource-1')
            ->shouldBeCalled()->willReturn([$seoDimension1->reveal(), $seoDimension2->reveal()]);

        $seoDimensionRepository->removeDimension($seoDimension1->reveal())->shouldBeCalled();
        $seoDimensionRepository->removeDimension($seoDimension2->reveal())->shouldBeCalled();

        $handler->__invoke($message->reveal());
    }
}
