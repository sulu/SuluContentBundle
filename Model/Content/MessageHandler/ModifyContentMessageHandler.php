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

use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierInterface;
use Sulu\Bundle\ContentBundle\Model\DimensionIdentifier\DimensionIdentifierRepositoryInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class ModifyContentMessageHandler
{
    /**
     * @var ContentDimensionRepositoryInterface
     */
    private $contentDimensionRepository;

    /**
     * @var DimensionIdentifierRepositoryInterface
     */
    private $dimensionIdentifierRepository;

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentDimensionRepositoryInterface $contentDimensionRepository,
        DimensionIdentifierRepositoryInterface $dimensionIdentifierRepository,
        StructureMetadataFactoryInterface $factory,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentDimensionRepository = $contentDimensionRepository;
        $this->dimensionIdentifierRepository = $dimensionIdentifierRepository;
        $this->factory = $factory;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(ModifyContentMessage $message): void
    {
        $draftContent = $this->findOrCreateDraftContentDimension($message->getResourceKey(), $message->getResourceId());
        $localizedDraftContent = $this->findOrCreateDraftContentDimension(
            $message->getResourceKey(),
            $message->getResourceId(),
            $message->getLocale()
        );

        $draftContent->setType($message->getType());
        $localizedDraftContent->setType($message->getType());

        $this->setData($message, $draftContent, $localizedDraftContent);

        $contentView = $this->contentViewFactory->create([$localizedDraftContent, $draftContent], $message->getLocale());
        if (!$contentView) {
            throw new ContentNotFoundException($message->getResourceKey(), $message->getResourceId());
        }

        $message->setContent($contentView);
    }

    private function setData(
        ModifyContentMessage $message,
        ContentDimensionInterface $draftContent,
        ContentDimensionInterface $localizedDraftContent
    ): void {
        $data = $message->getData();
        $metadata = $this->factory->getStructureMetadata($message->getResourceKey(), $message->getType());
        if (!$metadata) {
            return;
        }

        $localizedDraftData = [];
        $draftData = [];
        foreach ($metadata->getProperties() as $property) {
            $value = null;

            $name = $property->getName();
            if (is_float($name)) {
                $name = strval($name);
            }

            if (array_key_exists($name, $data)) {
                $value = $data[$name];
            }

            if ($property->isLocalized()) {
                $localizedDraftData[$name] = $value;

                continue;
            }

            $draftData[$name] = $value;
        }

        $localizedDraftContent->setData($localizedDraftData);
        $draftContent->setData($draftData);
    }

    private function findOrCreateDraftContentDimension(
        string $resourceKey,
        string $resourceId,
        ?string $locale = null
    ): ContentDimensionInterface {
        $dimensionIdentifier = $this->getDraftDimensionIdentifier($locale);

        return $this->contentDimensionRepository->findOrCreateDimension($resourceKey, $resourceId, $dimensionIdentifier);
    }

    private function getDraftDimensionIdentifier(?string $locale = null): DimensionIdentifierInterface
    {
        $attributes = [DimensionIdentifierInterface::ATTRIBUTE_KEY_STAGE => DimensionIdentifierInterface::ATTRIBUTE_VALUE_DRAFT];
        if ($locale) {
            $attributes[DimensionIdentifierInterface::ATTRIBUTE_KEY_LOCALE] = $locale;
        }

        return $this->dimensionIdentifierRepository->findOrCreateByAttributes($attributes);
    }
}
