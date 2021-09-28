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
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;

class RouteRemover implements EventSubscriber
{
    /**
     * @var RouteRepositoryInterface
     */
    private $routeRepository;

    public function __construct(RouteRepositoryInterface $routeRepository)
    {
        $this->routeRepository = $routeRepository;
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

        $entityClass = ClassUtils::getRealClass(\get_class($object));
        foreach ($this->routeRepository->findAllByEntity($entityClass, $object->getId()) as $route) {
            $event->getEntityManager()->remove($route);
        }
    }
}
