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

namespace Sulu\Bundle\ContentBundle\TestCases\Dimension;

use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;

trait DimensionRepositoryTestCaseTrait
{
    protected static $TEST_ID = '1234568-1234-1234-1234-123456789012';

    abstract protected static function purgeData(): void;

    abstract protected static function saveData(): void;

    /**
     * @return DimensionRepositoryInterface
     */
    abstract protected function getDimensionRepository();

    public static function setUpBeforeClass(): void
    {
        static::purgeData();
    }

    public function testCreate(): void
    {
        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->create();
        $this->assertInstanceOf(DimensionInterface::class, $dimension);
        $this->assertNotNull($dimension->getId());
    }

    public function testCreateWithUuid(): void
    {
        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->create(static::$TEST_ID);
        $this->assertInstanceOf(DimensionInterface::class, $dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());
    }

    public function testAddAndRemoveWithSave(): void
    {
        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->create(static::$TEST_ID);
        $dimensionRepository->add($dimension);
        static::saveData();

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNotNull($dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());

        $dimensionRepository->remove($dimension);
        static::saveData();

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNull($dimension);
    }

    public function testAddAndRemoveWithoutSave(): void
    {
        $this->markTestSkipped('Currently not implemented to findOneBy without saving.');

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->create(static::$TEST_ID);
        $dimensionRepository->add($dimension);

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNotNull($dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());

        $dimensionRepository->remove($dimension);

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNull($dimension);
    }

    public function testFindOneByNotExist(): void
    {
        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->findOneBy(['id' => 'none-exist-id']);
        $this->assertNull($dimension);
    }

    public function testFindOneBy(): void
    {
        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, 'de');
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(null, 'de', 'live');
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(null, 'en', 'draft');
        $dimensionRepository->add($dimension3);

        static::saveData();

        $dimension = $dimensionRepository->findOneBy(['locale' => 'de', 'workflowStage' => 'draft']);
        $this->assertNotNull($dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());
    }

    public function testFindByWithSave(): void
    {
        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, 'de', 'draft');
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(null, 'de', 'live');
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(null, 'en', 'draft');
        $dimensionRepository->add($dimension3);

        static::saveData();

        $dimensions = $dimensionRepository->findBy(['locale' => 'de']);
        $this->assertCount(2, $dimensions);
    }

    public function testFindByWithoutSave(): void
    {
        $this->markTestSkipped('Currently not implemented to findBy without saving.');

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, 'de', 'draft');
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(null, 'de', 'live');
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(null, 'en', 'draft');
        $dimensionRepository->add($dimension3);

        $dimensions = $dimensionRepository->findBy(['locale' => 'de']);
        $this->assertCount(2, $dimensions);
    }
}
