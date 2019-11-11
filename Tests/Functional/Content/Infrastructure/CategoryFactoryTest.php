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

namespace Sulu\Bundle\ContentBundle\Tests\Content\Infrastructure\Doctrine;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\TestCases\Content\CategoryFactoryTestCaseTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class CategoryFactoryTest extends BaseTestCase
{
    use CategoryFactoryTestCaseTrait;

    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
    }

    public function createCategoryFactory(): CategoryFactoryInterface
    {
        return self::$container->get('sulu_content.category_factory');
    }
}
