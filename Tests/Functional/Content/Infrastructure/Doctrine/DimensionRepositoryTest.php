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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class DimensionRepositoryTest extends BaseTestCase
{
    /**
     * @var string
     */
    protected static $TEST_ID = '1234568-1234-1234-1234-123456789011';

    /**
     * @var string
     */
    protected static $TEST_ID2 = '1234568-1234-1234-1234-123456789012';

    /**
     * @var string
     */
    protected static $TEST_ID3 = '1234568-1234-1234-1234-123456789013';

    /**
     * @var string
     */
    protected static $TEST_ID4 = '1234568-1234-1234-1234-123456789014';

    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    public static function setUpBeforeClass(): void
    {
        static::purgeData();
    }

    protected function setUp(): void
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

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testAddAndRemoveWithSave(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findOneBy without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->create(static::$TEST_ID);
        $dimensionRepository->add($dimension);
        if (!$skipSave) {
            static::saveData();
        }

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNotNull($dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());

        $dimensionRepository->remove($dimension);
        if (!$skipSave) {
            static::saveData();
        }

        $dimension = $dimensionRepository->findOneBy(['id' => static::$TEST_ID]);
        $this->assertNull($dimension);
    }

    public function testFindOneByNotExist(): void
    {
        $dimensionRepository = $this->getDimensionRepository();

        $dimension = $dimensionRepository->findOneBy(['id' => 'none-exist-id']);
        $this->assertNull($dimension);
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindOneByWithSave(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findOneBy without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'de']);
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(static::$TEST_ID2, ['locale' => 'de', 'stage' => 'live']);
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(static::$TEST_ID3, ['locale' => 'en', 'stage' => 'draft']);
        $dimensionRepository->add($dimension3);

        if (!$skipSave) {
            static::saveData();
        }

        $dimension = $dimensionRepository->findOneBy(['locale' => 'de', 'stage' => 'draft']);
        $this->assertNotNull($dimension);
        $this->assertSame(static::$TEST_ID, $dimension->getId());
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindByWithSave(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findBy without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'de']);
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(static::$TEST_ID2, ['locale' => 'de', 'stage' => 'live']);
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(static::$TEST_ID3, ['locale' => 'en', 'stage' => 'draft']);
        $dimensionRepository->add($dimension3);

        if (!$skipSave) {
            static::saveData();
        }

        $dimensions = $dimensionRepository->findBy(['locale' => 'de']);
        $this->assertCount(2, $dimensions);
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindAttributesWithLocaleAndStage(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findIdsByAttributes without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'en']);
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(static::$TEST_ID2, ['locale' => 'en-gb']);
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(static::$TEST_ID3, ['locale' => 'de', 'stage' => 'live']);
        $dimensionRepository->add($dimension3);

        $dimension4 = $dimensionRepository->create(static::$TEST_ID4);
        $dimensionRepository->add($dimension4);

        if (!$skipSave) {
            static::saveData();
        }

        $dimensions = iterator_to_array($dimensionRepository->findByAttributes([
            'locale' => 'en',
            'stage' => DimensionInterface::STAGE_DRAFT,
        ]));

        $this->assertCount(2, $dimensions);
        $this->assertSame([static::$TEST_ID4, static::$TEST_ID], array_map(function (DimensionInterface $dimension) {
            return $dimension->getId();
        }, $dimensions));
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindByAttributesWithStageOnly(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findByAttributes without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'en']);
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(static::$TEST_ID2, ['locale' => 'en-gb']);
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(static::$TEST_ID3, ['locale' => 'de', 'stage' => 'live']);
        $dimensionRepository->add($dimension3);

        $dimension4 = $dimensionRepository->create(static::$TEST_ID4);
        $dimensionRepository->add($dimension4);

        if (!$skipSave) {
            static::saveData();
        }

        $dimensions = iterator_to_array($dimensionRepository->findByAttributes([
            'stage' => DimensionInterface::STAGE_DRAFT,
        ]));

        $this->assertCount(1, $dimensions);
        $this->assertSame([static::$TEST_ID4], array_map(function (DimensionInterface $dimension) {
            return $dimension->getId();
        }, $dimensions));
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindByAttributesWithLocaleOnly(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findByAttributes without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'en']);
        $dimensionRepository->add($dimension1);

        $dimension2 = $dimensionRepository->create(static::$TEST_ID2, ['locale' => 'en-gb']);
        $dimensionRepository->add($dimension2);

        $dimension3 = $dimensionRepository->create(static::$TEST_ID3, ['locale' => 'en', 'stage' => 'live']);
        $dimensionRepository->add($dimension3);

        $dimension4 = $dimensionRepository->create(static::$TEST_ID4);
        $dimensionRepository->add($dimension4);

        if (!$skipSave) {
            static::saveData();
        }

        $dimensions = iterator_to_array($dimensionRepository->findByAttributes([
            'locale' => 'en',
        ]));

        $this->assertCount(2, $dimensions);
        $this->assertSame([static::$TEST_ID4, static::$TEST_ID], array_map(function (DimensionInterface $dimension) {
            return $dimension->getId();
        }, $dimensions));
    }

    /**
     * @dataProvider saveSkipDataProvider
     */
    public function testFindByAttributesIgnoreAdditionalAttributes(bool $skipSave): void
    {
        if ($skipSave) {
            $this->markTestSkipped('Currently not implemented to findByAttributes without saving.');
        }

        static::purgeData();

        $dimensionRepository = $this->getDimensionRepository();

        $dimension1 = $dimensionRepository->create(static::$TEST_ID, ['locale' => 'en']);
        $dimensionRepository->add($dimension1);

        if (!$skipSave) {
            static::saveData();
        }

        $dimensions = iterator_to_array($dimensionRepository->findByAttributes([
            'locale' => 'en',
            'any-parameter' => 'test',
        ]));

        $this->assertCount(1, $dimensions);
        $this->assertSame([static::$TEST_ID], array_map(function (DimensionInterface $dimension) {
            return $dimension->getId();
        }, $dimensions));
    }

    /**
     * @return \Generator<mixed[]>
     */
    public function saveSkipDataProvider(): \Generator
    {
        yield [
            false,
        ];

        yield [
            true,
        ];
    }
}
