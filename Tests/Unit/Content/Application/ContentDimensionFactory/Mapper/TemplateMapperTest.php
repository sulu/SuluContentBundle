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
use Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper\TemplateMapper;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class TemplateMapperTest extends TestCase
{
    protected function createTemplateMapperInstance(
        StructureMetadataFactoryInterface $structureMetadataFactory
    ): TemplateMapper {
        return new TemplateMapper($structureMetadataFactory);
    }

    public function testMapNoTemplateInstance(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());
        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoTemplateKey(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapLocalizedNoTemplateInstance(): void
    {
        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected "$localizedContentDimension" from type "%s" but "%s" given.',
            TemplateInterface::class,
            \get_class($localizedContentDimension->reveal())
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

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);
        $contentDimension->getTemplateType()->willReturn('example')->shouldBeCalled();

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
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

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);
        $contentDimension->getTemplateType()->willReturn('example')->shouldBeCalled();

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapUnlocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);
        $contentDimension->getTemplateType()->willReturn('example')->shouldBeCalled();
        $contentDimension->getTemplateData()->willReturn([])->shouldBeCalled();
        $contentDimension->setTemplateKey('template-key')->shouldBeCalled();
        $contentDimension->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

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

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal());
    }

    public function testMapLocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);
        $contentDimension->getTemplateType()->willReturn('example')->shouldBeCalled();
        $contentDimension->getTemplateData()->willReturn([])->shouldBeCalled();
        $contentDimension->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);
        $localizedContentDimension->setTemplateKey('template-key')->shouldBeCalled();
        $localizedContentDimension->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

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

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }

    public function testMapFloatValueTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            '1.1' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $contentDimension = $this->prophesize(ContentDimensionInterface::class);
        $contentDimension->willImplement(TemplateInterface::class);
        $contentDimension->getTemplateType()->willReturn('example')->shouldBeCalled();
        $contentDimension->getTemplateData()->willReturn([])->shouldBeCalled();
        $contentDimension->setTemplateData(['1.1' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedContentDimension = $this->prophesize(ContentDimensionInterface::class);
        $localizedContentDimension->willImplement(TemplateInterface::class);
        $localizedContentDimension->setTemplateKey('template-key')->shouldBeCalled();
        $localizedContentDimension->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

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

        $templateMapper = $this->createTemplateMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $contentDimension->reveal(), $localizedContentDimension->reveal());
    }
}
