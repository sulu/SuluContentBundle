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

namespace Sulu\Bundle\ContentBundle\Model\Content\MessageHandler;

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\RemoveContentMessage;

class RemoveContentMessageHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository
    ) {
        $this->contentDimensionRepository = $contentDimensionRepository;
    }

    public function __invoke(RemoveContentMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();

        $contentDimensions = $this->contentDimensionRepository->findByResource($resourceKey, $resourceId);

        foreach ($contentDimensions as $contentDimension) {
            $this->contentDimensionRepository->removeDimension($contentDimension);
        }
    }
}
