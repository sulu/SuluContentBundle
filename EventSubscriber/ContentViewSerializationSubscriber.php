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
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class ContentViewSerializationSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => Events::POST_SERIALIZE,
                'format' => 'json',
                'method' => 'onPostSerialize',
            ],
        ];
    }

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $factory;

    public function __construct(StructureMetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function onPostSerialize(ObjectEvent $event): void
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

            if (array_key_exists($name, $data)) {
                $visitor->setData((string) $name, $data[$name]);

                continue;
            }

            $visitor->setData((string) $name, null);
        }
    }
}
