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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\RouteRemover;
use Sulu\Bundle\RouteBundle\Entity\RouteRepositoryInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;

class RouteRemoverTest extends TestCase
{
    /**
     * @var RouteRepositoryInterface|ObjectProphecy
     */
    private $routeRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->routeRepository = $this->prophesize(RouteRepositoryInterface::class);
    }

    protected function getRouteRemover(): RouteRemover
    {
        return new RouteRemover($this->routeRepository->reveal());
    }

    public function testGetSubscribedEvents(): void
    {
        $routeRemover = $this->getRouteRemover();

        $this->assertSame([
            Events::preRemove,
        ], $routeRemover->getSubscribedEvents());
    }

    public function testPreRemove(): void
    {
        $object = $this->prophesize(ContentRichEntityInterface::class);
        $object->getId()->willReturn('123-123-123');

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $event = new LifecycleEventArgs($object->reveal(), $entityManager->reveal());

        $route1 = $this->prophesize(RouteInterface::class);
        $route2 = $this->prophesize(RouteInterface::class);
        $this->routeRepository->findAllByEntity(Argument::any(), '123-123-123')
            ->willReturn([$route1->reveal(), $route2->reveal()]);

        $entityManager->remove($route1->reveal())->shouldBeCalled();
        $entityManager->remove($route2->reveal())->shouldBeCalled();

        $routeRemover = $this->getRouteRemover();
        $routeRemover->preRemove($event);
    }

    public function testPreRemoveNoContentRichEntity(): void
    {
        $object = $this->prophesize(\stdClass::class);
        $event = $this->prophesize(LifecycleEventArgs::class);

        $event->getObject()->willReturn($object->reveal());

        $this->routeRepository->findAllByEntity(Argument::cetera())->shouldNotBeCalled();

        $routeRemover = $this->getRouteRemover();
        $routeRemover->preRemove($event->reveal());
    }
}
