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

namespace Sulu\Bundle\ContentBundle\Content\Application\MessageHandler;

use Sulu\Bundle\ContentBundle\Content\Application\Message\CopyContentDimensionMessage;
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentMessage;
use Sulu\Bundle\ContentBundle\Content\Application\Message\SaveContentMessage;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class CopyContentDimensionMessageHandler
{
    use HandleTrait;

    /**
     * @var MessageBusInterface
     */
    private $suluContentMessageBus;

    public function __construct(
        MessageBusInterface $suluContentMessageBus
    ) {
        $this->messageBus = $suluContentMessageBus;
    }

    public function __invoke(CopyContentDimensionMessage $message): array
    {
        $content = $message->getContent();
        $data = $this->handle(
            new LoadContentMessage($content, $message->getFromDimensionAttributes())
        );

        return $this->handle(
            new SaveContentMessage($content, $data, $message->getToDimensionAttributes())
        );
    }
}
