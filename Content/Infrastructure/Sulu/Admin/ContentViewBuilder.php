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

use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\PreviewFormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderRegistryInterface;

class ContentViewBuilder implements ContentViewBuilderInterface
{
    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    /**
     * @var PreviewObjectProviderRegistryInterface
     */
    private $objectProviderRegistry;

    public function __construct(
        ViewBuilderFactoryInterface $viewBuilderFactory,
        PreviewObjectProviderRegistryInterface $objectProviderRegistry
    ) {
        $this->viewBuilderFactory = $viewBuilderFactory;
        $this->objectProviderRegistry = $objectProviderRegistry;
    }

    public function build(
        ViewCollection $viewCollection,
        string $resourceKey,
        string $typeKey,
        string $editParentView,
        ?string $addParentView = null,
        ?ToolbarAction $saveToolbarAction = null
    ): void {
        // TODO check which interfaces the resource dimension implements and only add this tabs
        if (null === $saveToolbarAction) {
            $saveToolbarAction = new ToolbarAction(
                'sulu_admin.save_with_publishing',
                [
                    'publish_display_condition' => '(!_permissions || _permissions.live)',
                    'save_display_condition' => '(!_permissions || _permissions.edit)',
                ]
            );
        }

        // Add views
        if (null !== $addParentView) {
            $viewCollection->add(
                $this->buildTemplate($typeKey, $resourceKey, $addParentView, $saveToolbarAction, false)
                    ->setEditView($editParentView)
            );
        }

        $previewEnabled = $this->objectProviderRegistry->hasPreviewObjectProvider($resourceKey);

        // Edit views
        $viewCollection->add(
            $this->buildTemplate($typeKey, $resourceKey, $editParentView, $saveToolbarAction, $previewEnabled)
        );
        $viewCollection->add(
            $this->buildSeo($resourceKey, $editParentView, $saveToolbarAction, $previewEnabled)
        );
        $viewCollection->add(
            $this->buildExcerpt($resourceKey, $editParentView, $saveToolbarAction, $previewEnabled)
        );
    }

    /**
     * @return PreviewFormViewBuilderInterface|FormViewBuilderInterface
     */
    protected function buildTemplate(
        string $typeKey,
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction,
        bool $previewEnabled
    ): ViewBuilderInterface {
        $formToolbarActionsWithType = [
            $saveToolbarAction,
            new ToolbarAction(
                'sulu_admin.type',
                [
                    'disabled_condition' => '(_permissions && !_permissions.edit)',
                ]
            ),
            new ToolbarAction(
                'sulu_admin.delete',
                [
                    'display_condition' => '(!_permissions || _permissions.delete) && url != "/"',
                ]
            ),
        ];

        $formViewBuilder = $this->createFormViewBuilder($parentView . '.content', '/content', $previewEnabled);

        $formViewBuilder
            ->setResourceKey($resourceKey)
            ->setFormKey($typeKey)
            ->setTabTitle('sulu_content.content')
            ->addToolbarActions($formToolbarActionsWithType)
            ->setTabOrder(20)
            ->setParent($parentView);

        return $formViewBuilder;
    }

    /**
     * @return PreviewFormViewBuilderInterface|FormViewBuilderInterface
     */
    protected function buildSeo(
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction,
        bool $previewEnabled
    ): ViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            $saveToolbarAction,
        ];

        $formViewBuilder = $this->createFormViewBuilder($parentView . '.seo', '/seo', $previewEnabled);

        $formViewBuilder
            ->setResourceKey($resourceKey)
            ->setFormKey('content_seo')
            ->setTabTitle('sulu_content.seo')
            ->setTitleVisible(true)
            ->addToolbarActions($formToolbarActionsWithoutType)
            ->setTabOrder(30)
            ->setParent($parentView);

        return $formViewBuilder;
    }

    /**
     * @return PreviewFormViewBuilderInterface|FormViewBuilderInterface
     */
    protected function buildExcerpt(
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction,
        bool $previewEnabled
    ): ViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            $saveToolbarAction,
        ];

        $formViewBuilder = $this->createFormViewBuilder($parentView . '.excerpt', '/excerpt', $previewEnabled);

        $formViewBuilder
            ->setResourceKey($resourceKey)
            ->setFormKey('content_excerpt')
            ->setTabTitle('sulu_content.excerpt')
            ->setTitleVisible(true)
            ->addToolbarActions($formToolbarActionsWithoutType)
            ->setTabOrder(40)
            ->setParent($parentView);

        return $formViewBuilder;
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
