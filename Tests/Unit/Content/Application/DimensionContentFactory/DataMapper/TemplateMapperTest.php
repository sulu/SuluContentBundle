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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\DimensionContentFactory\DataMapper;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\DataMapper\TemplateDataMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class TemplateMapperTest extends TestCase
{
    protected function createTemplateDataMapperInstance(
        StructureMetadataFactoryInterface $structureMetadataFactory,
        array $structureDefaultTypes = []
    ): TemplateDataMapper {
        return new TemplateDataMapper($structureMetadataFactory, $structureDefaultTypes);
    }

    public function testMapNoTemplateInstance(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());
        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoTemplateKey(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedNoTemplateInstance(): void
    {
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected "$localizedDimensionContent" from type "%s" but "%s" given.',
            TemplateInterface::class,
            \get_class($localizedDimensionContent->reveal())
        ));

        $data = [
            'template' => 'template-key',
        ];

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedNoStructureFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find structure "template-key" of type "example".');

        $data = [
            'template' => 'template-key',
        ];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn(null)->shouldBeCalled();

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapUnlocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $dimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $dimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn('unlocalizedField')->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal());
    }

    public function testMapLocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $dimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn('unlocalizedField')->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();
        $localizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $localizedPropertyMetadata->getName()->willReturn('localizedField')->shouldBeCalled();
        $localizedPropertyMetadata->isLocalized()->willReturn(true)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
            $localizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapLocalizedNoTemplateKeyWithDefaultTemplate(): void
    {
        $data = [
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $dimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn('unlocalizedField')->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();
        $localizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $localizedPropertyMetadata->getName()->willReturn('localizedField')->shouldBeCalled();
        $localizedPropertyMetadata->isLocalized()->willReturn(true)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
            $localizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance(
            $structureMetadataFactory->reveal(),
            ['example' => 'template-key']
        );

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }

    public function testMapFloatValueTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            '1.1' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);
        $dimensionContent->getTemplateType()->willReturn('example')->shouldBeCalled();
        $dimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $dimensionContent->setTemplateData(['1.1' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn(1.1)->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();
        $localizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $localizedPropertyMetadata->getName()->willReturn('localizedField')->shouldBeCalled();
        $localizedPropertyMetadata->isLocalized()->willReturn(true)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
            $localizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'example',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContent->reveal(), $localizedDimensionContent->reveal());
    }
}
