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

namespace Sulu\Bundle\ContentBundle\EventSubscriber;

use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\JsonSerializationVisitor;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\Content\Compat\StructureManagerInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Serializer\ArraySerializationVisitor;

class ContentViewSerializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerializeJson',
            ],
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'array',
                'method' => 'onPostSerializeArray',
            ],
        ];
    }

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    /**
     * @var StructureManagerInterface
     */
    private $structureManager;

    /**
     * @var ContentTypeManagerInterface
     */
    private $contentTypeManager;

    public function __construct(
        StructureMetadataFactoryInterface $factory,
        StructureManagerInterface $structureManager,
        ContentTypeManagerInterface $contentTypeManager
    ) {
        $this->factory = $factory;
        $this->structureManager = $structureManager;
        $this->contentTypeManager = $contentTypeManager;
    }

    public function onPostSerializeJson(ObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof ContentViewInterface) {
            return;
        }

        $metadata = $this->factory->getStructureMetadata($object->getResourceKey(), $object->getType());
        if (!$metadata) {
            return;
        }
        $data = $object->getData();
        if (!$data) {
            return;
        }

        /** @var JsonSerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        foreach ($metadata->getProperties() as $property) {
            $name = $property->getName();
            if (\is_float($name)) {
                $name = (string) $name;
            }

            if (\array_key_exists($name, $data)) {
                $visitor->setData((string) $name, $data[$name]);

                continue;
            }

            $visitor->setData((string) $name, null);
        }
    }

    public function onPostSerializeArray(ObjectEvent $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof ContentViewInterface) {
            return;
        }

        $structure = $this->getStructure($object);
        if (!$structure) {
            return;
        }

        $data = $object->getData() ?? [];

        /** @var ArraySerializationVisitor $visitor */
        $visitor = $event->getVisitor();
        $visitor->setData('content', $this->resolveContent($structure, $data));
        $visitor->setData('view', $this->resolveView($structure, $data));
    }

    private function getStructure(ContentViewInterface $contentView): ?StructureInterface
    {
        $contentType = $contentView->getType();
        if (!$contentType) {
            return null;
        }

        $structure = $this->structureManager->getStructure($contentType, $contentView->getResourceKey());
        $structure->setLanguageCode($contentView->getLocale());

        return $structure;
    }

    private function resolveView(StructureInterface $structure, array $data): array
    {
        $view = [];
        foreach ($structure->getProperties(true) as $child) {
            if (\array_key_exists($child->getName(), $data)) {
                $child->setValue($data[$child->getName()]);
            }

            $contentType = $this->contentTypeManager->get($child->getContentTypeName());
            $view[$child->getName()] = $contentType->getViewData($child);
        }

        return $view;
    }

    private function resolveContent(StructureInterface $structure, array $data): array
    {
        $content = [];
        foreach ($structure->getProperties(true) as $child) {
            if (\array_key_exists($child->getName(), $data)) {
                $child->setValue($data[$child->getName()]);
            }

            $contentType = $this->contentTypeManager->get($child->getContentTypeName());
            $content[$child->getName()] = $contentType->getContentData($child);
        }

        return $content;
    }
}
