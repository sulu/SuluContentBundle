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

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Exception\ExcerptNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Excerpt\ExcerptDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Excerpt\Message\DuplicateExcerptMessage;

class DuplicateExcerptMessageHandler
{
    /**
     * @var ExcerptDimensionRepositoryInterface
     */
    private $excerptDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        ExcerptDimensionRepositoryInterface $excerptDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->excerptDimensionRepository = $excerptDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(DuplicateExcerptMessage $message): void
    {
        $attributes = [
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
        ];

        $dimensionIdentifiers = $this->dimensionIdentifierRepository->findByPartialAttributes($attributes);
        if (!$dimensionIdentifiers) {
            return;
        }

        $excerptDimensions = $this->excerptDimensionRepository->findByDimensionIdentifiers(
            $message->getResourceKey(),
            $message->getResourceId(),
            $dimensionIdentifiers
        );
        if (!$excerptDimensions) {
            throw new ExcerptNotFoundException(['resourceKey' => $message->getResourceKey(), 'resourceId' => $message->getResourceId()]);
        }

        foreach ($excerptDimensions as $excerptDimension) {
            $this->excerptDimensionRepository->createClone($excerptDimension, $message->getNewResourceId());
        }
    }
}
