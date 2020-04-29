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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\AdminBundle\Admin\View\DropdownToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistryInterface;

class ContentViewBuilderFactory implements ContentViewBuilderFactoryInterface
{
    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    /**
     * @var PreviewObjectProviderRegistryInterface
     */
    private $objectProviderRegistry;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        PreviewObjectProviderRegistryInterface $objectProviderRegistry,
        EntityManagerInterface $entityManager
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->objectProviderRegistry = $objectProviderRegistry;
        $this->entityManager = $entityManager;
    }

    public function getDefaultToolbarActions(): array
    {
        return [
            'save' => new ToolbarAction(
                'sulu_admin.save_with_publishing',
                [
                    'publish_visible_condition' => '(!_permissions || _permissions.live)',
                    'save_visible_condition' => '(!_permissions || _permissions.edit)',
                ]
            ),
            'type' => new ToolbarAction(
                'sulu_admin.type',
                [
                    'disabled_condition' => '(_permissions && !_permissions.edit)',
                ]
            ),
            'delete' => new ToolbarAction(
                'sulu_admin.delete',
                [
                    'visible_condition' => '(!_permissions || _permissions.delete) && url != "/"',
                ]
            ),
            'edit' => new DropdownToolbarAction(
                'sulu_admin.edit',
                'su-pen',
                [
                    new ToolbarAction(
                        'sulu_admin.delete_draft',
                        [
                            'visible_condition' => '(!_permissions || _permissions.live)',
                        ]
                    ),
                    new ToolbarAction(
                        'sulu_admin.set_unpublished',
                        [
                            'visible_condition' => '(!_permissions || _permissions.live)',
                        ]
                    ),
                ]
            ),
        ];
    }

    public function createContentRichViews(
        string $entityClass,
        string $templateFormKey,
        string $editParentView,
        ?string $addParentView = null,
        ?array $toolbarActions = null
    ): array {
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);
        $associationMapping = $classMetadata->getAssociationMapping('dimensionContents');
        $dimensionContentClass = $associationMapping['targetEntity'];

        /** @var callable $callable */
        $callable = [$entityClass, 'getResourceKey'];
        $resourceKey = \call_user_func($callable);
        $previewEnabled = $this->objectProviderRegistry->hasPreviewObjectProvider($resourceKey);

        $toolbarActions = $toolbarActions ?: $this->getDefaultToolbarActions();

        $views = [];

        if (is_subclass_of($dimensionContentClass, TemplateInterface::class)) {
            if ($addParentView) {
                $views[] = $this->createTemplateFormView(
                    $addParentView,
                    $previewEnabled,
                    $resourceKey,
                    $templateFormKey,
                    $toolbarActions
                );
            }

            $views[] = $this->createTemplateFormView(
                $editParentView,
                $previewEnabled,
                $resourceKey,
                $templateFormKey,
                $toolbarActions
            );
        }

        if (is_subclass_of($dimensionContentClass, SeoInterface::class)) {
            $views[] = $this->createSeoFormView(
                $editParentView,
                $previewEnabled,
                $resourceKey,
                ['save' => $toolbarActions['save']]
            );
        }

        if (is_subclass_of($dimensionContentClass, ExcerptInterface::class)) {
            $views[] = $this->createExcerptFormView(
                $editParentView,
                $previewEnabled,
                $resourceKey,
                ['save' => $toolbarActions['save']]
            );
        }

        return $views;
    }

    public function createTemplateFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        string $formKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface {
        return $this->createFormViewBuilder($parentView . '.content', '/content', $previewEnabled)
            ->setResourceKey($resourceKey)
            ->setFormKey($formKey)
            ->setTabTitle('sulu_content.content')
            ->addToolbarActions(array_values($toolbarActions ?: $this->getDefaultToolbarActions()))
            ->setTabOrder(20)
            ->setParent($parentView);
    }

    public function createSeoFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface {
        return $this->createFormViewBuilder($parentView . '.seo', '/seo', $previewEnabled)
            ->setResourceKey($resourceKey)
            ->setFormKey('content_seo')
            ->setTabTitle('sulu_content.seo')
            ->setTitleVisible(true)
            ->addToolbarActions(array_values($toolbarActions ?: [$this->getDefaultToolbarActions()['save']]))
            ->setTabOrder(30)
            ->setParent($parentView);
    }

    public function createExcerptFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface {
        return $this->createFormViewBuilder($parentView . '.excerpt', '/excerpt', $previewEnabled)
            ->setResourceKey($resourceKey)
            ->setFormKey('content_excerpt')
            ->setTabTitle('sulu_content.excerpt')
            ->setTitleVisible(true)
            ->addToolbarActions(array_values($toolbarActions ?: [$this->getDefaultToolbarActions()['save']]))
            ->setTabOrder(40)
            ->setParent($parentView);
    }

    /**
     * @return PreviewFormViewBuilderInterface|FormViewBuilderInterface
     */
    protected function createFormViewBuilder(string $name, string $path, bool $previewEnabled): ViewBuilderInterface
    {
        if ($previewEnabled) {
            $formViewBuilder = $this->viewBuilderFactory->createPreviewFormViewBuilder($name, $path);
        } else {
            $formViewBuilder = $this->viewBuilderFactory->createFormViewBuilder($name, $path);
        }

        return $formViewBuilder;
    }
}
