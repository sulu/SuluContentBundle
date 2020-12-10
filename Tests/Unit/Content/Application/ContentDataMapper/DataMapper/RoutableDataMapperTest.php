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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentDataMapper\DataMapper;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\RoutableDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\RoutableInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\MockWrapper;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\RoutableMockWrapperTrait;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\TemplateMockWrapperTrait;
use Sulu\Bundle\RouteBundle\Generator\RouteGeneratorInterface;
use Sulu\Bundle\RouteBundle\Manager\ConflictResolverInterface;
use Sulu\Bundle\RouteBundle\Manager\RouteManagerInterface;
use Sulu\Bundle\RouteBundle\Model\RouteInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class RoutableDataMapperTest extends TestCase
{
    /**
     * @param array<string, string> $structureDefaultTypes
     * @param array<string, array<mixed>> $resourceKeyMappings
     */
    protected function createRouteDataMapperInstance(
        StructureMetadataFactoryInterface $factory,
        RouteGeneratorInterface $routeGenerator,
        RouteManagerInterface $routeManager,
        ConflictResolverInterface $conflictResolver,
        array $structureDefaultTypes = [],
        array $resourceKeyMappings = []
    ): RoutableDataMapper {
        if (empty($resourceKeyMappings)) {
            $resourceKeyMappings = [
                'mock-resource-key' => [
                    'generator' => 'schema',
                    'options' => [
                        'route_schema' => '/{object["title"]}',
                    ],
                    'resource_key' => 'mock-resource-key',
                    'entityClass' => 'mock-content-class',
                ],
            ];
        }

        return new RoutableDataMapper($factory, $routeGenerator, $routeManager, $conflictResolver, $structureDefaultTypes, $resourceKeyMappings);
    }

    /**
     * @param ObjectProphecy<DimensionContentInterface> $routableMock
     */
    protected function wrapRoutableMock(ObjectProphecy $routableMock): RoutableInterface
    {
        return new class($routableMock) extends MockWrapper implements
            TemplateInterface,
            RoutableInterface {
            use TemplateMockWrapperTrait;
            use RoutableMockWrapperTrait;
        };
    }

    public function testMapNoRoutable(): void
    {
        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map($data, $dimensionContent->reveal(), null);
    }

    public function testMapNoTemplateInterface(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('LocalizedObject needs to extend the TemplateInterface');

        $data = [];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->getResourceId()->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $factory->getStructureMetadata(Argument::cetera())->shouldNotBeCalled();
        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->getResourceId()->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn(null)->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('text_line');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn('de');
        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateData()->willReturn(['url' => '/test']);
        $localizedDimensionContent->getLocale()->willReturn('de');
        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContent->setTemplateData(['url' => '/test'])->shouldBeCalled();
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $routeGenerator->generate(
            array_merge($data, [
                '_unlocalizedObject' => $dimensionContent->reveal(),
                '_localizedObject' => $localizedDimensionContentMock,
            ]),
            ['route_schema' => '/{object["title"]}']
        )->willReturn('/test');

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/test');
        $routeManager->createOrUpdateByAttributes(
            'mock-content-class',
            '123-123-123',
            'en',
            '/test'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['url' => '/test'])->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $routeGenerator->generate(
            array_merge($data, [
                '_unlocalizedObject' => $dimensionContent->reveal(),
                '_localizedObject' => $localizedDimensionContentMock,
            ]),
            ['route_schema' => '/{object["title"]}']
        )->willReturn('/');

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn(null);

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $routeManager->createOrUpdateByAttributes(Argument::cetera())->shouldNotBeCalled();
        $conflictResolver->resolve(Argument::cetera())->shouldNotBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn(null);

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $this->wrapRoutableMock($localizedDimensionContent)
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateData()->willReturn(['title' => 'Test', 'url' => null]);
        $localizedDimensionContent->getLocale()->willReturn('en');

        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $routeGenerator->generate(
            array_merge($data, [
                '_unlocalizedObject' => $dimensionContent->reveal(),
                '_localizedObject' => $localizedDimensionContentMock,
            ]),
            ['route_schema' => '/{object["title"]}']
        )->willReturn('/test');

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/test');
        $routeManager->createOrUpdateByAttributes(
            'mock-content-class',
            '123-123-123',
            'en',
            '/test'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['title' => 'Test', 'url' => '/test'])->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $routeGenerator->generate(Argument::any())->shouldNotBeCalled();

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/test');
        $routeManager->createOrUpdateByAttributes(
            'mock-content-class',
            '123-123-123',
            'en',
            '/test'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
    }

    public function testMapConflictingRoute(): void
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $routeGenerator->generate(Argument::any())->shouldNotBeCalled();

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/test-1');

        $routeManager->createOrUpdateByAttributes(
            'mock-content-class',
            '123-123-123',
            'en',
            '/test'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['url' => '/test-1'])->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal()
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
    }

    public function testMapNoTemplateWithDefaultTemplate(): void
    {
        $data = [
            'url' => '/test',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(RoutableInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);

        $factory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $routeGenerator = $this->prophesize(RouteGeneratorInterface::class);
        $routeManager = $this->prophesize(RouteManagerInterface::class);
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->willReturn([$property->reveal()]);

        $localizedDimensionContent->getTemplateData()->willReturn([]);
        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $routeGenerator->generate(Argument::any())->shouldNotBeCalled();

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/test');
        $routeManager->createOrUpdateByAttributes(
            'mock-content-class',
            '123-123-123',
            'en',
            '/test'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(Argument::cetera())->shouldNotBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal(),
            ['mock-template-type' => 'default']
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
    }

    public function testMapCustomRoute(): void
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
        $conflictResolver = $this->prophesize(ConflictResolverInterface::class);

        $metadata = $this->prophesize(StructureMetadata::class);
        $property = $this->prophesize(PropertyMetadata::class);
        $property->getType()->willReturn('route');
        $property->getName()->willReturn('url');

        $metadata->getProperties()->WillReturn([$property->reveal()]);

        $factory->getStructureMetadata('mock-template-type', 'default')->willReturn($metadata->reveal())->shouldBeCalled();

        $localizedDimensionContent->getResourceId()->willReturn('123-123-123');
        $localizedDimensionContent->getTemplateData()->willReturn(['title' => 'Test', 'url' => null]);
        $localizedDimensionContent->getLocale()->willReturn('en');
        $localizedDimensionContentMock = $this->wrapRoutableMock($localizedDimensionContent);

        $routeGenerator->generate(
            array_merge($data, [
                '_unlocalizedObject' => $dimensionContent->reveal(),
                '_localizedObject' => $localizedDimensionContentMock,
            ]),
            ['route_schema' => 'custom/{object["_localizedObject"].getName()}-{object["_unlocalizedObject"].getResourceId()}']
        )->willReturn('/custom/testEntity-123');

        $route = $this->prophesize(RouteInterface::class);
        $route->getPath()->willReturn('/custom/testEntity-123');
        $routeManager->createOrUpdateByAttributes(
            'Sulu/Test/TestEntity',
            '123-123-123',
            'en',
            '/custom/testEntity-123'
        )->willReturn($route->reveal());

        $conflictResolver->resolve($route->reveal())->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['title' => 'Test', 'url' => '/custom/testEntity-123'])->shouldBeCalled();

        $mapper = $this->createRouteDataMapperInstance(
            $factory->reveal(),
            $routeGenerator->reveal(),
            $routeManager->reveal(),
            $conflictResolver->reveal(),
            [],
            [
                'mock-resource-key' => [
                    'generator' => 'schema',
                    'options' => [
                        'route_schema' => 'custom/{object["_localizedObject"].getName()}-{object["_unlocalizedObject"].getResourceId()}',
                    ],
                    'resource_key' => 'mock-resource-key',
                    'entityClass' => 'Sulu/Test/TestEntity',
                ],
            ]
        );

        $mapper->map(
            $data,
            $dimensionContent->reveal(),
            $localizedDimensionContentMock
        );
    }
}
