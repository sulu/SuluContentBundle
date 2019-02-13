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

namespace Sulu\Bundle\ContentBundle\Model\Content\Message;

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;

class DuplicateContentMessageHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository
    ) {
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
    }

    public function __invoke(DuplicateContentMessage $message): void
    {
        $attributes = [
            DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT,
        ];
        $dimensionIdentifier = $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);

        $contentDimensions = $this->contentDimensionRepository->findByDimensionIdentifiers(
            $message->getResourceKey(),
            $message->getNewResourceId(),
            [$dimensionIdentifier]
        );
        if (!$contentDimensions) {
            throw new ContentNotFoundException($message->getResourceKey(), $message->getNewResourceId());
        }

        foreach ($contentDimensions as $contentDimension) {
            $this->contentDimensionRepository->createClone($contentDimension, $message->getNewResourceId());
        }
    }
}
