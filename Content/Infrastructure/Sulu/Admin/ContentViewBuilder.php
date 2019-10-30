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

use Doctrine\Common\Inflector\Inflector;
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
        string $editParentView,
        ?string $addParentView = null
    ): void {
        // TODO check which interfaces the resource dimension implements and only add this templates

        // Add views
        if (null !== $addParentView) {
            $viewCollection->add(
                $this->buildTemplate($resourceKey, $addParentView)
                    ->setEditView($editParentView)
            );
        }

        // Edit views
        $viewCollection->add($this->buildTemplate($resourceKey, $editParentView));
        $viewCollection->add($this->buildSeo($resourceKey, $editParentView));
        $viewCollection->add($this->buildExcerpt($resourceKey, $editParentView));
    }

    protected function buildTemplate(
        string $resourceKey,
        string $parentView
    ): FormViewBuilderInterface {
        $inflector = new Inflector();
        $typeKey = $inflector->singularize($resourceKey);

        $formToolbarActionsWithType = [
            new ToolbarAction('sulu_admin.save_with_publishing'),
            new ToolbarAction('sulu_admin.type'),
            new ToolbarAction('sulu_admin.delete'),
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
        string $parentView
    ): FormViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            new ToolbarAction('sulu_admin.save_with_publishing'),
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
        string $parentView
    ): FormViewBuilderInterface {
        $formToolbarActionsWithoutType = [
            new ToolbarAction('sulu_admin.save_with_publishing'),
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
