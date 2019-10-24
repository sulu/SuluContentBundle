<?php

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Dimension\Domain\Repository;

use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

abstract class DimensionRepositoryTestCase extends BaseTestCase
{
    const TEST_ID = '1234568-1234-1234-1234-123456789012';

    /**
     * @var DimensionRepositoryInterface
     */
    protected $dimensionRepository;

    abstract protected static function purgeData(): void;

    abstract protected static function saveData(): void;

    public static function setUpBeforeClass(): void
    {
        static::purgeData();
    }

    public function testCreate(): void
    {
        $dimension = $this->dimensionRepository->create();
        $this->assertInstanceOf(DimensionInterface::class, $dimension);
        $this->assertNotNull($dimension->getId());
    }

    public function testCreateWithUuid(): void
    {
        $dimension = $this->dimensionRepository->create(self::TEST_ID);
        $this->assertInstanceOf(DimensionInterface::class, $dimension);
        $this->assertSame(self::TEST_ID, $dimension->getId());
    }

    public function testAddAndRemove(): void
    {
        static::purgeData();

        $dimension = $this->dimensionRepository->create(self::TEST_ID);
        $this->dimensionRepository->add($dimension);
        static::saveData();

        $dimension = $this->dimensionRepository->findOneBy(['id' => self::TEST_ID]);
        $this->assertNotNull($dimension);
        $this->assertSame(self::TEST_ID, $dimension->getId());

        $this->dimensionRepository->remove($dimension);
        static::saveData();

        $dimension = $this->dimensionRepository->findOneBy(['id' => self::TEST_ID]);
        $this->assertNull($dimension);
    }

    public function testFindOneByNotExist(): void
    {
        $dimension = $this->dimensionRepository->findOneBy(['id' => 'none-exist-id']);
        $this->assertNull($dimension);
    }

    public function testFindOneBy(): void
    {
        static::purgeData();

        $dimension1 = $this->dimensionRepository->create(self::TEST_ID, 'de');
        $this->dimensionRepository->add($dimension1);

        $dimension2 = $this->dimensionRepository->create(null, 'de', true);
        $this->dimensionRepository->add($dimension2);

        $dimension3 = $this->dimensionRepository->create(null, 'en', false);
        $this->dimensionRepository->add($dimension3);

        static::saveData();

        $dimension = $this->dimensionRepository->findOneBy(['locale' => 'de', 'published' => false]);
        $this->assertNotNull($dimension);
        $this->assertSame(self::TEST_ID, $dimension->getId());
    }

    public function testFindBy(): void
    {
        static::purgeData();

        $dimension1 = $this->dimensionRepository->create(self::TEST_ID, 'de', false);
        $this->dimensionRepository->add($dimension1);

        $dimension2 = $this->dimensionRepository->create(null, 'de', true);
        $this->dimensionRepository->add($dimension2);

        $dimension3 = $this->dimensionRepository->create(null, 'en', false);
        $this->dimensionRepository->add($dimension3);

        static::saveData();

        $dimensions = $this->dimensionRepository->findBy(['locale' => 'de']);
        $this->assertCount(2, $dimensions);
    }
}
