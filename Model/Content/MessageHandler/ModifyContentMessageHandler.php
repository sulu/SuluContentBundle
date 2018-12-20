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

use Sulu\Bundle\ContentBundle\Model\Content\ContentInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Model\Content\Factory\ContentViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Model\Content\Message\ModifyContentMessage;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionInterface;
use Sulu\Bundle\ContentBundle\Model\Dimension\DimensionRepositoryInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class ModifyContentMessageHandler
{
    /**
     * @var ContentRepositoryInterface
     */
    private $contentRepository;

    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var ContentViewFactoryInterface
     */
    private $contentViewFactory;

    public function __construct(
        ContentRepositoryInterface $contentRepository,
        DimensionRepositoryInterface $dimensionRepository,
        StructureMetadataFactoryInterface $factory,
        ContentViewFactoryInterface $contentViewFactory
    ) {
        $this->contentRepository = $contentRepository;
        $this->dimensionRepository = $dimensionRepository;
        $this->factory = $factory;
        $this->contentViewFactory = $contentViewFactory;
    }

    public function __invoke(ModifyContentMessage $message): void
    {
        $draftContent = $this->findOrCreateContent($message->getResourceKey(), $message->getResourceId());
        $localizedDraftContent = $this->findOrCreateContent(
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
        ContentInterface $draftContent,
        ContentInterface $localizedDraftContent
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

    private function findOrCreateContent(
        string $resourceKey,
        string $resourceId,
        ?string $locale = null
    ): ContentInterface {
        $dimension = $this->dimensionRepository->findOrCreateByAttributes($this->createAttributes($locale));

        return $this->contentRepository->findOrCreate($resourceKey, $resourceId, $dimension);
    }

    /**
     * @return string[]
     */
    private function createAttributes(?string $locale = null): array
    {
        $attributes = [DimensionInterface::ATTRIBUTE_KEY_STAGE => DimensionInterface::ATTRIBUTE_VALUE_DRAFT];
        if (!$locale) {
            return $attributes;
        }

        $attributes[DimensionInterface::ATTRIBUTE_KEY_LOCALE] = $locale;

        return $attributes;
    }
}
