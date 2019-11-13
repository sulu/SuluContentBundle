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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\TestCases\Dimension\DimensionRepositoryTestCaseTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class DimensionRepositoryTest extends BaseTestCase
{
    use DimensionRepositoryTestCaseTrait;

    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    public function setUp(): void
    {
        self::bootKernel();
        self::$entityManager = self::$container->get('doctrine.orm.entity_manager');
    }

    protected static function purgeData(): void
    {
        self::purgeDatabase();
    }

    protected static function saveData(): void
    {
        self::$entityManager->flush();
    }

    protected function getDimensionRepository(): DimensionRepositoryInterface
    {
        return self::$container->get('sulu.repository.dimension');
    }
}
