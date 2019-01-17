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

namespace Sulu\Bundle\ContentBundle\Model\Excerpt\MessageHandler;

use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\RemoveExcerptMessage;

class RemoveExcerptMessageHandler
{
    /**
     * @var ExcerptDimensionRepositoryInterface
     */
    private $excerptDimensionRepository;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
    }

    public function __invoke(RemoveExcerptMessage $message): void
    {
        $resourceKey = $message->getResourceKey();
        $resourceId = $message->getResourceId();

        $excerptDimensions = $this->excerptDimensionRepository->findByResource($resourceKey, $resourceId);

        foreach ($excerptDimensions as $excerptDimension) {
            $this->excerptDimensionRepository->removeDimension($excerptDimension);
        }
    }
}
