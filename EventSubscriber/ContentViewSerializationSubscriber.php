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
use JMS\Serializer\Metadata\StaticPropertyMetadata;
use JMS\Serializer\Visitor\SerializationVisitorInterface;
use Sulu\Bundle\ContentBundle\Model\Content\ContentViewInterface;
use Sulu\Component\Content\Compat\StructureInterface;
use Sulu\Component\Content\Compat\StructureManagerInterface;
use Sulu\Component\Content\ContentTypeManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class ContentViewSerializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'method' => 'onPostSerialize',
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

    public function onPostSerialize(ObjectEvent $event): void
    {
        if ($event->getContext()->hasAttribute('array_serializer')) {
            $this->onPostSerializeArray($event);
        } else {
            $this->onPostSerializeJson($event);
        }
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

        /** @var SerializationVisitorInterface $visitor */
        $visitor = $event->getVisitor();
        foreach ($metadata->getProperties() as $property) {
            $name = (string) $property->getName();
            $value = $data[$name] ?? null;

            $visitor->visitProperty(
                new StaticPropertyMetadata('', $name, $value),
                $value
            );
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

        /** @var SerializationVisitorInterface $visitor */
        $visitor = $event->getVisitor();

        $content = $this->resolveContent($structure, $data);
        $visitor->visitProperty(
            new StaticPropertyMetadata('', 'content', $content),
            $content
        );

        $view = $this->resolveView($structure, $data);
        $visitor->visitProperty(
            new StaticPropertyMetadata('', 'view', $view),
            $view
        );
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
