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
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper\TemplateDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\DimensionContentMockWrapperTrait;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\MockWrapper;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\TemplateMockWrapperTrait;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class TemplateDataMapperTest extends TestCase
{
    /**
     * @var ObjectProphecy|StructureMetadataFactoryInterface
     */
    private $structureMetadataFactory;

    protected function setUp(): void
    {
        $this->structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
    }

    /**
     * @param array<string, string> $structureDefaultTypes
     */
    protected function createTemplateDataMapperInstance(
        array $structureDefaultTypes = []
    ): TemplateDataMapper {
        return new TemplateDataMapper($this->structureMetadataFactory->reveal(), $structureDefaultTypes);
    }

    public function testMapNoTemplateInstance(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'title' => 'Test Localized',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $templateMapper = $this->createTemplateDataMapperInstance();
        $templateMapper->map($unlocalizedDimensionContent->reveal(), $localizedDimensionContent->reveal(), $data);

        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapNoTemplateKey(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'unlocalizedField' => 'Test Unlocalized',
            'title' => 'Test Localized',
        ];

        $example = new Example();
        $unlocalizedDimensionContent = new ExampleDimensionContent($example);
        $localizedDimensionContent = new ExampleDimensionContent($example);

        $templateMapper = $this->createTemplateDataMapperInstance();
        $templateMapper->map($unlocalizedDimensionContent, $localizedDimensionContent, $data);
    }

    public function testMapNoStructureFound(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [
            'template' => 'none-exist-template',
            'unlocalizedField' => 'Test Unlocalized',
            'title' => 'Test Localized',
        ];

        $example = new Example();
        $unlocalizedDimensionContent = new ExampleDimensionContent($example);
        $localizedDimensionContent = new ExampleDimensionContent($example);

        $templateMapper = $this->createTemplateDataMapperInstance();
        $templateMapper->map($unlocalizedDimensionContent, $localizedDimensionContent, $data);
    }

    public function testMapNoData(): void
    {
        $data = [];

        $example = new Example();
        $unlocalizedDimensionContent = new ExampleDimensionContent($example);
        $localizedDimensionContent = new ExampleDimensionContent($example);

        $this->structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($this->createStructureMetadata())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance(['example' => 'template-key']);
        $templateMapper->map($unlocalizedDimensionContent, $localizedDimensionContent, $data);

        $this->assertNull($unlocalizedDimensionContent->getTemplateKey());
        $this->assertNull($localizedDimensionContent->getTemplateKey());
        $this->assertSame(['title' => null], $unlocalizedDimensionContent->getTemplateData());
        $this->assertSame(['title' => null], $localizedDimensionContent->getTemplateData());
    }

    public function testMapData(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'title' => 'Test Localized',
        ];

        $example = new Example();
        $unlocalizedDimensionContent = new ExampleDimensionContent($example);
        $localizedDimensionContent = new ExampleDimensionContent($example);

        $this->structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($this->createStructureMetadata())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance();
        $templateMapper->map($unlocalizedDimensionContent, $localizedDimensionContent, $data);

        $this->assertNull($unlocalizedDimensionContent->getTemplateKey());
        $this->assertSame('template-key', $localizedDimensionContent->getTemplateKey());
        $this->assertSame(['unlocalizedField' => 'Test Unlocalized', 'title' => null], $unlocalizedDimensionContent->getTemplateData());
        $this->assertSame(['title' => 'Test Localized', 'title' => null], $localizedDimensionContent->getTemplateData());
    }

    public function testMapWithDefaultTemplate(): void
    {
        $data = [
            'unlocalizedField' => 'Test Unlocalized',
            'title' => 'Test Localized',
        ];

        $example = new Example();
        $unlocalizedDimensionContent = new ExampleDimensionContent($example);
        $localizedDimensionContent = new ExampleDimensionContent($example);

        $this->structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($this->createStructureMetadata())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance([
            'example' => 'template-key',
        ]);
        $templateMapper->map($unlocalizedDimensionContent, $localizedDimensionContent, $data);

        $this->assertNull($unlocalizedDimensionContent->getTemplateKey());
        $this->assertSame('template-key', $localizedDimensionContent->getTemplateKey());
        $this->assertSame(['unlocalizedField' => 'Test Unlocalized', 'title' => null], $unlocalizedDimensionContent->getTemplateData());
        $this->assertSame(['title' => 'Test Localized', 'title' => null], $localizedDimensionContent->getTemplateData());
    }

    private function createStructureMetadata(): StructureMetadata
    {
        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn('unlocalizedField')->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();
        $localizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $localizedPropertyMetadata->getName()->willReturn('title')->shouldBeCalled();
        $localizedPropertyMetadata->isLocalized()->willReturn(true)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
            $localizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        return $structureMetadata->reveal();
    }
}
