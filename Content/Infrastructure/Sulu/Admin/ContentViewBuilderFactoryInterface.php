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

use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderInterface;

interface ContentViewBuilderFactoryInterface
{
    /**
     * @return array<string, ToolbarAction>
     */
    public function getDefaultToolbarActions(): array;

    /**
     * @param array<string, ToolbarAction> $toolbarActions
     *
     * @return ViewBuilderInterface[]
     */
    public function createViews(
        string $entityClass,
        string $editParentView,
        ?string $addParentView = null,
        ?string $securityContext = null,
        ?array $toolbarActions = null
    ): array;

    /**
     * @param array<string, ToolbarAction> $toolbarActions
     */
    public function createTemplateFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        string $formKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface;

    /**
     * @param array<string, ToolbarAction> $toolbarActions
     */
    public function createSeoFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface;

    /**
     * @param array<string, ToolbarAction> $toolbarActions
     */
    public function createExcerptFormView(
        string $parentView,
        bool $previewEnabled,
        string $resourceKey,
        ?array $toolbarActions = null
    ): ViewBuilderInterface;
}
