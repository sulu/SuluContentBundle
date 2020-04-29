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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactory;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactory;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilderFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistry;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistryInterface;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;

class ContentViewBuilderFactoryTest extends TestCase
{
    protected function createContentViewBuilder(
        EntityManagerInterface $entityManager,
        SecurityCheckerInterface $securityChecker,
        PreviewObjectProviderRegistryInterface $previewObjectProviderRegistry = null
    ): ContentViewBuilderFactoryInterface {
        if (null === $previewObjectProviderRegistry) {
            $previewObjectProviderRegistry = $this->createPreviewObjectProviderRegistry([]);
        }

        return new ContentViewBuilderFactory(
            new ViewBuilderFactory(),
            $previewObjectProviderRegistry,
            $entityManager,
            $securityChecker
        );
    }

    /**
     * @param array<string, PreviewObjectProviderInterface> $providers
     */
    protected function createPreviewObjectProviderRegistry(array $providers): PreviewObjectProviderRegistryInterface
    {
        return new PreviewObjectProviderRegistry($providers);
    }

    protected function createContentObjectProvider(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentDataMapperInterface $contentDataMapper,
        string $entityClass
    ): ContentObjectProvider {
        return new ContentObjectProvider(
            $entityManager,
            $contentResolver,
            $contentDataMapper,
            $entityClass
        );
    }

    public function testCreateViews(): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getAssociationMapping('dimensionContents')
            ->willReturn(['targetEntity' => ExampleDimensionContent::class]);

        $entityManager->getClassMetadata(Example::class)->willReturn($classMetadata->reveal());

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        $views = $contentViewBuilder->createViews(Example::class, 'edit_parent_key');

        $this->assertCount(3, $views);

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[0]);
        $this->assertSame('edit_parent_key.content', $views[0]->getName());
        $this->assertSame(Example::TEMPLATE_TYPE, $views[0]->getView()->getOption('formKey'));

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[1]);
        $this->assertSame('edit_parent_key.seo', $views[1]->getName());
        $this->assertSame('content_seo', $views[1]->getView()->getOption('formKey'));

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[2]);
        $this->assertSame('edit_parent_key.excerpt', $views[2]->getName());
        $this->assertSame('content_excerpt', $views[2]->getView()->getOption('formKey'));

        $views = $contentViewBuilder->createViews(Example::class, 'edit_parent_key', 'add_parent_key');

        $this->assertCount(4, $views);

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[0]);
        $this->assertSame('add_parent_key.content', $views[0]->getName());
        $this->assertSame(Example::TEMPLATE_TYPE, $views[0]->getView()->getOption('formKey'));

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[1]);
        $this->assertSame('edit_parent_key.content', $views[1]->getName());
        $this->assertSame(Example::TEMPLATE_TYPE, $views[1]->getView()->getOption('formKey'));

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[2]);
        $this->assertSame('edit_parent_key.seo', $views[2]->getName());
        $this->assertSame('content_seo', $views[2]->getView()->getOption('formKey'));

        $this->assertInstanceOf(FormViewBuilderInterface::class, $views[3]);
        $this->assertSame('edit_parent_key.excerpt', $views[3]->getName());
        $this->assertSame('content_excerpt', $views[3]->getView()->getOption('formKey'));
    }

    public function testCreateViewsWithPreview(): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getAssociationMapping('dimensionContents')
            ->willReturn(['targetEntity' => ExampleDimensionContent::class]);

        $entityManager->getClassMetadata(Example::class)->willReturn($classMetadata->reveal());

        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);

        $contentObjectProvider = $this->createContentObjectProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentDataMapper->reveal(),
            Example::class
        );

        $previewObjectProviders = ['examples' => $contentObjectProvider];
        $previewObjectProviderRegistry = $this->createPreviewObjectProviderRegistry($previewObjectProviders);
        $contentViewBuilder = $this->createContentViewBuilder(
            $entityManager->reveal(),
            $securityChecker->reveal(),
            $previewObjectProviderRegistry
        );

        $views = $contentViewBuilder->createViews(Example::class, 'edit_parent_key');

        $this->assertCount(3, $views);
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $views[0]);
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $views[1]);
        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $views[2]);
    }

    /**
     * @return mixed[]
     */
    public function getSecurityContextData(): array
    {
        return [
            [
                [
                    PermissionTypes::ADD => true,
                    PermissionTypes::EDIT => true,
                    PermissionTypes::LIVE => true,
                    PermissionTypes::DELETE => true,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
            [
                [
                    PermissionTypes::ADD => false,
                    PermissionTypes::EDIT => true,
                    PermissionTypes::LIVE => true,
                    PermissionTypes::DELETE => true,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
            [
                [
                    PermissionTypes::ADD => false,
                    PermissionTypes::EDIT => true,
                    PermissionTypes::LIVE => false,
                    PermissionTypes::DELETE => false,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
            [
                [
                    PermissionTypes::ADD => true,
                    PermissionTypes::EDIT => false,
                    PermissionTypes::LIVE => true,
                    PermissionTypes::DELETE => true,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                ],
            ],
            [
                [
                    PermissionTypes::ADD => true,
                    PermissionTypes::EDIT => true,
                    PermissionTypes::LIVE => false,
                    PermissionTypes::DELETE => true,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete'],
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
            [
                [
                    PermissionTypes::ADD => true,
                    PermissionTypes::EDIT => true,
                    PermissionTypes::LIVE => true,
                    PermissionTypes::DELETE => false,
                ],
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $permissions
     * @param mixed[] $expectedTypes
     *
     * @dataProvider getSecurityContextData
     */
    public function testCreateViewsWithSecurityContext(array $permissions, array $expectedTypes): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getAssociationMapping('dimensionContents')->willReturn(
                ['targetEntity' => ExampleDimensionContent::class]
            );

        $entityManager->getClassMetadata(Example::class)->willReturn($classMetadata->reveal());

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        foreach ($permissions as $permissionType => $permission) {
            $securityChecker->hasPermission('test_context', $permissionType)->willReturn($permission);
        }

        $views = $contentViewBuilder->createViews(
            Example::class,
            'edit_parent_key',
            'add_parent_key',
            'test_context'
        );

        $this->assertCount(\count($expectedTypes), $views);

        foreach ($views as $index => $viewBuilder) {
            $toolbarActions = $viewBuilder->getView()->getOption('toolbarActions');

            $this->assertCount(\count($expectedTypes[$index]), $toolbarActions);
            foreach ($expectedTypes[$index] as $action => $type) {
                $this->assertSame($type, $toolbarActions[$action]->getType());
            }
        }
    }

    public function testCreateTemplateFormView(): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        $viewBuilderWithoutPreview = $contentViewBuilder->createTemplateFormView(
            'edit_parent_key',
            false,
            'examples',
            'example_detail'
        );

        $this->assertInstanceOf(FormViewBuilderInterface::class, $viewBuilderWithoutPreview);
        $this->assertSame('edit_parent_key', $viewBuilderWithoutPreview->getView()->getParent());
        $this->assertSame('examples', $viewBuilderWithoutPreview->getView()->getOption('resourceKey'));
        $this->assertSame('example_detail', $viewBuilderWithoutPreview->getView()->getOption('formKey'));

        $viewBuilderWithPreview = $contentViewBuilder->createTemplateFormView(
            'edit_parent_key',
            true,
            'examples',
            'example_detail'
        );

        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $viewBuilderWithPreview);

        $toolbarActions = ['save' => new ToolbarAction('test_type')];
        $viewBuilderWithToolbarButtons = $contentViewBuilder->createTemplateFormView(
            'edit_parent_key',
            false,
            'examples',
            'example_detail',
            $toolbarActions
        );

        $this->assertSame(
            array_values($toolbarActions),
            $viewBuilderWithToolbarButtons->getView()->getOption('toolbarActions')
        );
    }

    public function testCreateSeoFormView(): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        $viewBuilderWithoutPreview = $contentViewBuilder->createSeoFormView(
            'edit_parent_key',
            false,
            'examples'
        );

        $this->assertInstanceOf(FormViewBuilderInterface::class, $viewBuilderWithoutPreview);
        $this->assertSame('edit_parent_key', $viewBuilderWithoutPreview->getView()->getParent());
        $this->assertSame('examples', $viewBuilderWithoutPreview->getView()->getOption('resourceKey'));
        $this->assertSame('content_seo', $viewBuilderWithoutPreview->getView()->getOption('formKey'));

        $viewBuilderWithPreview = $contentViewBuilder->createTemplateFormView(
            'edit_parent_key',
            true,
            'examples',
            'example_detail'
        );

        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $viewBuilderWithPreview);

        $toolbarActions = ['save' => new ToolbarAction('test_type')];
        $viewBuilderWithToolbarButtons = $contentViewBuilder->createTemplateFormView(
            'edit_parent_key',
            false,
            'examples',
            'example_detail',
            $toolbarActions
        );

        $this->assertSame(
            array_values($toolbarActions),
            $viewBuilderWithToolbarButtons->getView()->getOption('toolbarActions')
        );
    }

    public function testCreateExcerptFormView(): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        $viewBuilderWithoutPreview = $contentViewBuilder->createSeoFormView(
            'edit_parent_key',
            false,
            'examples'
        );

        $this->assertInstanceOf(FormViewBuilderInterface::class, $viewBuilderWithoutPreview);
        $this->assertSame('edit_parent_key', $viewBuilderWithoutPreview->getView()->getParent());
        $this->assertSame('examples', $viewBuilderWithoutPreview->getView()->getOption('resourceKey'));
        $this->assertSame('content_seo', $viewBuilderWithoutPreview->getView()->getOption('formKey'));

        $viewBuilderWithPreview = $contentViewBuilder->createSeoFormView(
            'edit_parent_key',
            true,
            'examples'
        );

        $this->assertInstanceOf(PreviewFormViewBuilderInterface::class, $viewBuilderWithPreview);

        $toolbarActions = ['save' => new ToolbarAction('test_type')];
        $viewBuilderWithToolbarButtons = $contentViewBuilder->createSeoFormView(
            'edit_parent_key',
            false,
            'examples',
            $toolbarActions
        );

        $this->assertSame(
            array_values($toolbarActions),
            $viewBuilderWithToolbarButtons->getView()->getOption('toolbarActions')
        );
    }
}
