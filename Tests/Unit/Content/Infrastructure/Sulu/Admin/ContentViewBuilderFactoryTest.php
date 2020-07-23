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
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactory;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;
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

    /**
     * @param class-string<ContentRichEntityInterface> $entityClass
     */
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
     * @param mixed[] $expectedToolbarActions
     *
     * @dataProvider getSecurityContextData
     */
    public function testCreateViewsWithSecurityContext(array $permissions, array $expectedToolbarActions): void
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

        $this->assertCount(\count($expectedToolbarActions), $views);

        foreach ($views as $index => $viewBuilder) {
            $toolbarActions = $viewBuilder->getView()->getOption('toolbarActions');
            $toolbarActionTypes = array_map(function ($toolbarAction) {
                return $toolbarAction->getType();
            }, $toolbarActions);

            $this->assertSame($expectedToolbarActions[$index], $toolbarActionTypes);
        }
    }

    /**
     * @return mixed[]
     */
    public function getContentRichEntityClassData(): array
    {
        return [
            [
                new class() implements DimensionContentInterface, SeoInterface, ExcerptInterface {
                    use DimensionContentTrait;
                    use SeoTrait;
                    use ExcerptTrait;

                    public function getContentRichEntity(): ContentRichEntityInterface
                    {
                        throw new \RuntimeException('Should not be called while executing tests.');
                    }
                },
                [
                    ['sulu_admin.save'],
                    ['sulu_admin.save'],
                ],
            ],
            [
                new class() implements DimensionContentInterface, TemplateInterface, SeoInterface, ExcerptInterface {
                    use DimensionContentTrait;
                    use TemplateTrait;
                    use SeoTrait;
                    use ExcerptTrait;

                    public function getContentRichEntity(): ContentRichEntityInterface
                    {
                        throw new \RuntimeException('Should not be called while executing tests.');
                    }

                    public static function getTemplateType(): string
                    {
                        return 'mock-template-type';
                    }
                },
                [
                    ['sulu_admin.save', 'sulu_admin.type', 'sulu_admin.delete'],
                    ['sulu_admin.save', 'sulu_admin.type', 'sulu_admin.delete'],
                    ['sulu_admin.save'],
                    ['sulu_admin.save'],
                ],
            ],
            [
                new class() implements DimensionContentInterface, TemplateInterface, WorkflowInterface, SeoInterface, ExcerptInterface {
                    use DimensionContentTrait;
                    use TemplateTrait;
                    use WorkflowTrait;
                    use SeoTrait;
                    use ExcerptTrait;

                    public function getContentRichEntity(): ContentRichEntityInterface
                    {
                        throw new \RuntimeException('Should not be called while executing tests.');
                    }

                    public static function getTemplateType(): string
                    {
                        return 'mock-template-type';
                    }
                },
                [
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing', 'sulu_admin.type', 'sulu_admin.delete', 'sulu_admin.dropdown'],
                    ['sulu_admin.save_with_publishing'],
                    ['sulu_admin.save_with_publishing'],
                ],
            ],
        ];
    }

    /**
     * @param mixed[] $expectedToolbarActions
     *
     * @dataProvider getContentRichEntityClassData
     */
    public function testCreateViewsWithContentRichEntityClass(DimensionContentInterface $dimensionContentObject, array $expectedToolbarActions): void
    {
        $securityChecker = $this->prophesize(SecurityCheckerInterface::class);

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getAssociationMapping('dimensionContents')->willReturn(
            ['targetEntity' => \get_class($dimensionContentObject)]
        );

        $entityManager->getClassMetadata(Example::class)->willReturn($classMetadata->reveal());

        $contentViewBuilder = $this->createContentViewBuilder($entityManager->reveal(), $securityChecker->reveal());

        $views = $contentViewBuilder->createViews(
            Example::class,
            'edit_parent_key',
            'add_parent_key'
        );

        $this->assertCount(\count($expectedToolbarActions), $views);

        foreach ($views as $index => $viewBuilder) {
            $toolbarActions = $viewBuilder->getView()->getOption('toolbarActions');
            $toolbarActionTypes = array_map(function ($toolbarAction) {
                return $toolbarAction->getType();
            }, $toolbarActions);

            $this->assertSame($expectedToolbarActions[$index], $toolbarActionTypes);
        }
    }
}
