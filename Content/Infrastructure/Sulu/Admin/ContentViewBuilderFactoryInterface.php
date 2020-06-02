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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

interface ContentViewBuilderFactoryInterface
{
    /**
     * @return array<string, ToolbarAction>
     */
    public function getDefaultToolbarActions(): array;

    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     * @param array<string, ToolbarAction> $toolbarActions
     *
     * @return ViewBuilderInterface[]
     */
    public function createViews(
        string $contentRichEntityClass,
        string $editParentView,
        ?string $addParentView = null,
        ?string $securityContext = null,
        ?array $toolbarActions = null
    );
}
