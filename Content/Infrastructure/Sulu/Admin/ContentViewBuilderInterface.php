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

use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;

interface ContentViewBuilderInterface
{
    public function build(
        ViewCollection $viewCollection,
        string $resourceKey,
        string $typeKey,
        string $editParentView,
        string $addParentView = null
    ): void;
}
