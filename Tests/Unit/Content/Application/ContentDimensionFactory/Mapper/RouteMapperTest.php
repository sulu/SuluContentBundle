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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDimensionFactory\Mapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\RouteMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\RouteBundle\Generator\RouteGeneratorInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class RouteMapperTest extends TestCase
{
    protected function createRouteMapperInstance(
        StructureMetadataFactoryInterface $factory,
        RouteGeneratorInterface $routeGenerator,
        RouteManagerInterface $routeManager
    ): RouteMapper {
        return new RouteMapper($factory, $routeGenerator, $routeManager);
    }

    public function testMapNoRoutable(): void
    {
        $data = [];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoLocalizedDimension(): void
    {
        $data = [];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), null);
    }

    public function testMapNoTemplateInterface(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $localizedContentDimension->getContentId()->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoTemplate(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $localizedContentDimension->getContentId()->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoMetadata(): void
    {
        $data = [
            'template' => 'default',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $localizedContentDimension->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn(null)->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoRouteProperty(): void
    {
        $data = [
            'template' => 'default',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('text_line');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getContentId()->willReturn('123-123-123');
        $localizedContentDimension->getTemplateType()->willReturn('example');
        $localizedContentDimension->getLocale()->willReturn('de');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoRoutePropertyData(): void
    {
        $data = [
            'template' => 'default',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getContentId()->willReturn('123-123-123');
        $localizedContentDimension->getTemplateType()->willReturn('example');
        $localizedContentDimension->getLocale()->willReturn('de');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoContentId(): void
    {
        $data = [
            'template' => 'default',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedContentDimension->getContentId()->willReturn(null);

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoLocale(): void
    {
        $data = [
            'template' => 'default',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedContentDimension->getContentId()->willReturn('123-123-123');
        $localizedContentDimension->getLocale()->willReturn(null);

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapNoRoutePath(): void
    {
        $data = [
            'template' => 'default',
            'url' => null,
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedContentDimension->getContentId()->willReturn('123-123-123');
        $localizedContentDimension->getContentClass()->willReturn('App\Entity\Example');

        $localizedContentDimension->getTemplateData()->willReturn(['title' => 'Test', 'url' => null]);
        $localizedContentDimension->setTemplateData(['title' => 'Test', 'url' => '/test'])->shouldBeCalled();

        $localizedContentDimension->getLocale()->willReturn('en');

        $routeGenerator->generate($localizedContentDimension, ['route_schema' => '/{object.getTitle()}'])
            ->willReturn('/test');

        $routeManager->createOrUpdateByAttributes(
            'App\Entity\Example',
            '123-123-123',
            'en',
            '/test'
        )->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMap(): void
    {
        $data = [
            'template' => 'default',
            'url' => '/test',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(RoutableInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedContentDimension->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedContentDimension->getContentId()->willReturn('123-123-123');
        $localizedContentDimension->getContentClass()->willReturn('App\Entity\Example');
        $localizedContentDimension->getLocale()->willReturn('en');

        $routeGenerator->generate($localizedContentDimension, ['schema' => '/{object.getTitle()}'])
            ->shouldNotBeCalled();

        $routeManager->createOrUpdateByAttributes(
            'App\Entity\Example',
            '123-123-123',
            'en',
            '/test'
        )->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }
}
