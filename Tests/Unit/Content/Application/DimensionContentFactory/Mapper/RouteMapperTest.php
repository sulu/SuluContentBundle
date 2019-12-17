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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\DimensionContentFactory\Mapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper\RouteMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
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

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

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

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoLocalizedDimension(): void
    {
        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

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

        $mapper->map($data, $dimensionContent->reveal(), null);
    }

    public function testMapNoTemplateInterface(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->getContentId()->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoTemplate(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->getContentId()->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoMetadata(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn(null)->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoRouteProperty(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('text_line');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $localizedDimensionContent->getLocale()->willReturn('de');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoRoutePropertyData(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $localizedDimensionContent->getTemplateData()->willReturn(['url' => '/test']);
        $localizedDimensionContent->getLocale()->willReturn('de');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoRoutePropertyDataAndNoOldRoute(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getContentClass()->willReturn('App\Entity\Example');

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $localizedDimensionContent->getLocale()->willReturn('en');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $routeGenerator->generate($localizedDimensionContent, ['route_schema' => '/{object.getTitle()}'])
            ->willReturn('/test');

        $localizedDimensionContent->setTemplateData(['url' => '/test'])->shouldBeCalled();

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

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoRoutePropertyDataAndNoOldRouteIgnoreSlash(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getContentClass()->willReturn('App\Entity\Example');

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $localizedDimensionContent->getLocale()->willReturn('en');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $routeGenerator->generate($localizedDimensionContent, ['route_schema' => '/{object.getTitle()}'])
            ->willReturn('/');

        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoContentId(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getContentId()->willReturn(null);

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoLocale(): void
    {
        $data = [
            'template' => 'default',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn(null);

        $mapper = $this->createRouteMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapNoRoutePath(): void
    {
        $data = [
            'template' => 'default',
            'url' => null,
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getContentClass()->willReturn('App\Entity\Example');

        $localizedDimensionContent->getTemplateData()->willReturn(['title' => 'Test', 'url' => null]);
        $localizedDimensionContent->setTemplateData(['title' => 'Test', 'url' => '/test'])->shouldBeCalled();

        $localizedDimensionContent->getLocale()->willReturn('en');

        $routeGenerator->generate($localizedDimensionContent, ['route_schema' => '/{object.getTitle()}'])
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

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMap(): void
    {
        $data = [
            'template' => 'default',
            'url' => '/test',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateType()->willReturn('example');
        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $factory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getContentId()->willReturn('123-123-123');
        $localizedDimensionContent->getContentClass()->willReturn('App\Entity\Example');
        $localizedDimensionContent->getLocale()->willReturn('en');

        $routeGenerator->generate($localizedDimensionContent, ['schema' => '/{object.getTitle()}'])
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

        $mapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }
}
