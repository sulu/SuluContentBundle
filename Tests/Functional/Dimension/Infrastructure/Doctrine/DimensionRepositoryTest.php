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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Dimension\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Infrastructure\Doctrine\DimensionRepository;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class DimensionRepositoryTest extends BaseTestCase
{
    const TEST_UUID = '1234568-1234-1234-1234-123456789012';

    /**
     * @var DimensionRepository
     */
    private $dimensionRepository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public static function setUpBeforeClass(): void
    {
        self::purgeDatabase();
    }

    public function setUp(): void
    {
        self::bootKernel();
        $this->dimensionRepository = self::$container->get('sulu.repository.dimension');
        $this->entityManager = self::$container->get('doctrine.orm.entity_manager');
    }

    public function testCreate(): void
    {
        $dimension = $this->dimensionRepository->create();
        $this->assertInstanceOf(Dimension::class, $dimension);
        $this->assertNotNull($dimension->getId());
    }

    public function testCreateWithUuid(): void
    {
        $dimension = $this->dimensionRepository->create(self::TEST_UUID);
        $this->assertInstanceOf(Dimension::class, $dimension);
        $this->assertSame(self::TEST_UUID, $dimension->getId());
    }

    public function testAddAndRemove(): void
    {
        self::purgeDatabase();

        $dimension = $this->dimensionRepository->create(self::TEST_UUID);
        $this->dimensionRepository->add($dimension);
        $this->entityManager->flush();

        $dimension = $this->dimensionRepository->findOneBy(['id' => self::TEST_UUID]);
        $this->assertNotNull($dimension);
        $this->assertSame(self::TEST_UUID, $dimension->getId());

        $this->dimensionRepository->remove($dimension);
        $this->entityManager->flush();

        $dimension = $this->dimensionRepository->findOneBy(['id' => self::TEST_UUID]);
        $this->assertNull($dimension);
    }

    public function testFindOneByNotExist(): void
    {
        $dimension = $this->dimensionRepository->findOneBy(['id' => 'none-exist-id']);
        $this->assertNull($dimension);
    }

    public function testFindOneBy(): void
    {
        self::purgeDatabase();

        $dimension1 = $this->dimensionRepository->create(self::TEST_UUID);
        $dimension1->setLocale('de');
        $dimension1->setPublished(false);
        $this->dimensionRepository->add($dimension1);

        $dimension2 = $this->dimensionRepository->create();
        $dimension2->setLocale('de');
        $dimension2->setPublished(true);
        $this->dimensionRepository->add($dimension2);

        $dimension3 = $this->dimensionRepository->create();
        $dimension3->setLocale('en');
        $dimension3->setPublished(false);
        $this->dimensionRepository->add($dimension2);

        $this->entityManager->flush();

        $dimension = $this->dimensionRepository->findOneBy(['locale' => 'de', 'published' => false]);
        $this->assertNotNull($dimension);
        $this->assertSame(self::TEST_UUID, $dimension->getId());
    }

    public function testFindBy(): void
    {
        self::purgeDatabase();

        $dimension1 = $this->dimensionRepository->create(self::TEST_UUID);
        $dimension1->setLocale('de');
        $dimension1->setPublished(false);
        $this->dimensionRepository->add($dimension1);

        $dimension2 = $this->dimensionRepository->create();
        $dimension2->setLocale('de');
        $dimension2->setPublished(true);
        $this->dimensionRepository->add($dimension2);

        $dimension3 = $this->dimensionRepository->create();
        $dimension3->setLocale('en');
        $dimension3->setPublished(false);
        $this->dimensionRepository->add($dimension2);

        $this->entityManager->flush();

        $dimensions = $this->dimensionRepository->findBy(['locale' => 'de']);
        $this->assertCount(2, $dimensions);
    }
}
