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
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;

class ContentViewBuilder implements ContentViewBuilderInterface
{
    /**
     * @var ViewBuilderFactoryInterface
     */
    private $viewBuilderFactory;

    public function __construct(ViewBuilderFactoryInterface $viewBuilderFactory)
    {
        $this->viewBuilderFactory = $viewBuilderFactory;
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
                $this->buildTemplate($typeKey, $resourceKey, $addParentView, $saveToolbarAction)
                    ->setEditView($editParentView)
            );
        }

        // Edit views
        $viewCollection->add($this->buildTemplate($typeKey, $resourceKey, $editParentView, $saveToolbarAction));
        $viewCollection->add($this->buildSeo($resourceKey, $editParentView, $saveToolbarAction));
        $viewCollection->add($this->buildExcerpt($resourceKey, $editParentView, $saveToolbarAction));
    }

    protected function buildTemplate(
        string $typeKey,
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction
    ): FormViewBuilderInterface {
        $formToolbarActionsWithType = [
            $saveToolbarAction,
            new ToolbarAction(
                'sulu_admin.type',
                [
                    'disable_condition' => '(_permissions && !_permissions.edit)',
                ]
            ),
            new ToolbarAction(
                'sulu_admin.delete',
                [
                    'display_condition' => '(!_permissions || _permissions.delete) && url != "/"',
                ]
            ),
        ];

        $formViewBuilder = $this->viewBuilderFactory
            ->createFormViewBuilder($parentView . '.content', '/content');

        $formViewBuilder
            ->setResourceKey($resourceKey)
            ->setFormKey($typeKey)
            ->setTabTitle('sulu_content.content')
            ->addToolbarActions($formToolbarActionsWithType)
            ->setTabOrder(20)
            ->setParent($parentView);

        return $formViewBuilder;
    }

    protected function buildSeo(
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction
    ): FormViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            $saveToolbarAction,
        ];

        $formViewBuilder = $this->viewBuilderFactory
            ->createFormViewBuilder($parentView . '.seo', '/seo');

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

    protected function buildExcerpt(
        string $resourceKey,
        string $parentView,
        ToolbarAction $saveToolbarAction
    ): FormViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            $saveToolbarAction,
        ];

        $formViewBuilder = $this->viewBuilderFactory
            ->createFormViewBuilder($parentView . '.excerpt', '/excerpt');

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
}
