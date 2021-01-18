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
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\DimensionContentMockWrapperTrait;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\MockWrapper;
use Sulu\Bundle\ContentBundle\Tests\Unit\Mocks\TemplateMockWrapperTrait;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class TemplateDataMapperTest extends TestCase
{
    /**
     * @param array<string, string> $structureDefaultTypes
     */
    protected function createTemplateDataMapperInstance(
        StructureMetadataFactoryInterface $structureMetadataFactory,
        array $structureDefaultTypes = []
    ): TemplateDataMapper {
        return new TemplateDataMapper($structureMetadataFactory, $structureDefaultTypes);
    }

    /**
     * @param ObjectProphecy<DimensionContentInterface> $tempplateMock
     */
    protected function wrapTemplateMock(ObjectProphecy $tempplateMock): TemplateInterface
    {
        return new class($tempplateMock) extends MockWrapper implements
            DimensionContentInterface,
            TemplateInterface {
            use DimensionContentMockWrapperTrait;
            use TemplateMockWrapperTrait;
        };
    }

    public function testMapNoTemplateInstance(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($unlocalizedDimensionContent);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());
        $templateMapper->map($data, $dimensionContentCollection->reveal());

        $this->assertTrue(true); // Avoid risky test as this is an early return test
    }

    public function testMapLocalizedNoTemplateKey(): void
    {
        $this->expectException(\RuntimeException::class);

        $data = [];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedNoTemplateInstance(): void
    {
        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected "$localizedObject" from type "%s" but "%s" given.',
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
            'mock-template-type',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedNoStructureFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not find structure "template-key" of type "mock-template-type".');

        $data = [
            'template' => 'template-key',
        ];

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'mock-template-type',
            'template-key'
        )->willReturn(null)->shouldBeCalled();

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($localizedDimensionContent);

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapUnlocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);
        $unlocalizedDimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $unlocalizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $unlocalizedDimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn(null);

        $unlocalizedPropertyMetadata = $this->prophesize(PropertyMetadata::class);
        $unlocalizedPropertyMetadata->getName()->willReturn('unlocalizedField')->shouldBeCalled();
        $unlocalizedPropertyMetadata->isLocalized()->willReturn(false)->shouldBeCalled();

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getProperties()->willReturn([
            $unlocalizedPropertyMetadata->reveal(),
        ])->shouldBeCalled();

        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $structureMetadataFactory->getStructureMetadata(
            'mock-template-type',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);
        $unlocalizedDimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $unlocalizedDimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($this->wrapTemplateMock($localizedDimensionContent));

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
            'mock-template-type',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapLocalizedNoTemplateKeyWithDefaultTemplate(): void
    {
        $data = [
            'unlocalizedField' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);
        $unlocalizedDimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $unlocalizedDimensionContent->setTemplateData(['unlocalizedField' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($this->wrapTemplateMock($localizedDimensionContent));

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
            'mock-template-type',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance(
            $structureMetadataFactory->reveal(),
            ['mock-template-type' => 'template-key']
        );

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }

    public function testMapFloatValueTemplate(): void
    {
        $data = [
            'template' => 'template-key',
            '1.1' => 'Test Unlocalized',
            'localizedField' => 'Test Localized',
        ];

        $unlocalizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $unlocalizedDimensionContent->willImplement(TemplateInterface::class);
        $unlocalizedDimensionContent->getTemplateData()->willReturn([])->shouldBeCalled();
        $unlocalizedDimensionContent->setTemplateData(['1.1' => 'Test Unlocalized'])->shouldBeCalled();

        $localizedDimensionContent = $this->prophesize(DimensionContentInterface::class);
        $localizedDimensionContent->willImplement(TemplateInterface::class);
        $localizedDimensionContent->setTemplateKey('template-key')->shouldBeCalled();
        $localizedDimensionContent->setTemplateData(['localizedField' => 'Test Localized'])->shouldBeCalled();

        $dimensionContentCollection = $this->prophesize(DimensionContentCollectionInterface::class);
        $dimensionContentCollection->getDimensionAttributes()->willReturn(['stage' => 'draft', 'locale' => 'de']);
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => null])
            ->willReturn($this->wrapTemplateMock($unlocalizedDimensionContent));
        $dimensionContentCollection->getDimensionContent(['stage' => 'draft', 'locale' => 'de'])
            ->willReturn($this->wrapTemplateMock($localizedDimensionContent));

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
            'mock-template-type',
            'template-key'
        )->willReturn($structureMetadata->reveal())->shouldBeCalled();

        $templateMapper = $this->createTemplateDataMapperInstance($structureMetadataFactory->reveal());

        $templateMapper->map($data, $dimensionContentCollection->reveal());
    }
}
