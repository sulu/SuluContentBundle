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

namespace Sulu\Bundle\ContentBundle\Tests\Functional;

use Sulu\Bundle\CategoryBundle\Entity\CategoryRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Tests\Traits\AssertSnapshotTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

abstract class BaseTestCase extends SuluTestCase
{
    use AssertSnapshotTrait;

    protected static function getContentManager(): ContentManagerInterface
    {
        return static::getContainer()->get('sulu_content.content_manager');
    }

    protected static function getCategoryRepository(): CategoryRepositoryInterface
    {
        return static::getContainer()->get('sulu.repository.category');
    }
}
