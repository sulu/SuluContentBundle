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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMetadataInspector\ContentMetadataInspectorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;

/**
 * @internal
 *
 * @final
 */
class RouteRemover implements EventSubscriber
{
    /**
     * @var ContentMetadataInspectorInterface
     */
    private $contentMetadataInspector;

    /**
     * @var RouteRepositoryInterface
     */
    private $routeRepository;

    /**
     * @var array<string, array<mixed>>
     */
    private $routeMappings;

    /**
     * @param array<string, array<mixed>> $routeMappings
     */
    public function __construct(
        ContentMetadataInspectorInterface $contentMetadataInspector,
        RouteRepositoryInterface $routeRepository,
        array $routeMappings
    ) {
        $this->routeRepository = $routeRepository;
        $this->contentMetadataInspector = $contentMetadataInspector;
        $this->routeMappings = $routeMappings;
    }

    public function getSubscribedEvents()
    {
        return [Events::preRemove];
    }

    public function preRemove(LifecycleEventArgs $event): void
    {
        $object = $event->getObject();
        if (!$object instanceof ContentRichEntityInterface) {
            return; // @codeCoverageIgnore
        }

        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass(\get_class($object));
        $resourceKey = $dimensionContentClass::getResourceKey();

        $entityClass = null;
        foreach ($this->routeMappings as $key => $mapping) {
            if ($resourceKey === $mapping['resource_key']) {
                $entityClass = $mapping['entityClass'] ?? $key;
                break;
            }
        }

        if (!$entityClass) {
            return;
        }

        foreach ($this->routeRepository->findAllByEntity($entityClass, $object->getId()) as $route) {
            $event->getEntityManager()->remove($route);
        }
    }
}
