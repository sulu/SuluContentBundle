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

namespace Sulu\Bundle\ContentBundle\Model\Seo\MessageHandler;

use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Seo\Exception\SeoNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\DuplicateSeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoDimensionRepositoryInterface;

class DuplicateSeoMessageHandler
{
    /**
     * @var SeoDimensionRepositoryInterface
     */
    private $seoDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        SeoDimensionRepositoryInterface $seoDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->seoDimensionRepository = $seoDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(DuplicateSeoMessage $message): void
    {
        $attributes = [
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
        ];

        $dimensionIdentifiers = $this->dimensionIdentifierRepository->findByPartialAttributes($attributes);
        if (!$dimensionIdentifiers) {
            return;
        }

        $seoDimensions = $this->seoDimensionRepository->findByDimensionIdentifiers(
            $message->getResourceKey(),
            $message->getResourceId(),
            $dimensionIdentifiers
        );
        if (!$seoDimensions) {
            throw new SeoNotFoundException($message->getResourceKey(), $message->getResourceId());
        }

        foreach ($seoDimensions as $seoDimension) {
            $this->seoDimensionRepository->createClone($seoDimension, $message->getNewResourceId());
        }
    }
}
