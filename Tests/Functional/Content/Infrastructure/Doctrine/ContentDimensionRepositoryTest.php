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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\ContentDimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimension;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class ContentDimensionRepositoryTest extends BaseTestCase
{
    public function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
    }

    public function createContentDimensionRepository(): ContentDimensionRepositoryInterface
    {
        return self::$container->get('sulu_content.content_dimension_repository');
    }

    public function testLoadExistAll(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);
        $dimension3 = $this->createDimension('789-456', ['locale' => 'en']);

        $contentRichEntity = $this->createContentRichEntity();
        $contentDimension1 = $this->createContentDimension($contentRichEntity, $dimension1);
        $contentDimension2 = $this->createContentDimension($contentRichEntity, $dimension2);
        $this->createContentDimension($contentRichEntity, $dimension3);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $contentDimensionRepository = $this->createContentDimensionRepository();
        $contentDimensionCollection = $contentDimensionRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(2, $contentDimensionCollection);

        $this->assertSame([
            $contentDimension1->getId(),
            $contentDimension2->getId(),
        ], array_map(function (ContentDimensionInterface $contentDimension) {
            return $contentDimension->getId();
        }, iterator_to_array($contentDimensionCollection)));
    }

    public function testLoadOneNotExist(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);

        $contentRichEntity = $this->createContentRichEntity();
        $contentDimension1 = $this->createContentDimension($contentRichEntity, $dimension1);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $contentDimensionRepository = $this->createContentDimensionRepository();
        $contentDimensionCollection = $contentDimensionRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(1, $contentDimensionCollection);

        $this->assertSame([
            $contentDimension1->getId(),
        ], array_map(function (ContentDimensionInterface $contentDimension) {
            return $contentDimension->getId();
        }, iterator_to_array($contentDimensionCollection)));
    }

    public function testLoadExistOrderedDifferent(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);

        $contentRichEntity = $this->createContentRichEntity();
        // First create the dimension 2 to test if its still the last dimension
        $contentDimension2 = $this->createContentDimension($contentRichEntity, $dimension2);
        $contentDimension1 = $this->createContentDimension($contentRichEntity, $dimension1);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $contentDimensionRepository = $this->createContentDimensionRepository();
        $contentDimensionCollection = $contentDimensionRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(2, $contentDimensionCollection);

        $this->assertSame([
            $contentDimension1->getId(),
            $contentDimension2->getId(), // Dimension 2 should be the last one in this case
        ], array_map(function (ContentDimensionInterface $contentDimension) {
            return $contentDimension->getId();
        }, iterator_to_array($contentDimensionCollection)));
    }

    /**
     * @param mixed[] $attributes
     */
    private function createDimension(string $id, array $attributes): DimensionInterface
    {
        $dimension = new Dimension($id, $attributes);
        $this->getEntityManager()->persist($dimension);

        return $dimension;
    }

    private function createContentRichEntity(): Example
    {
        $example = new Example();
        $this->getEntityManager()->persist($example);

        return $example;
    }

    private function createContentDimension(Example $example, DimensionInterface $dimension): ExampleDimension
    {
        $exampleDimension = new ExampleDimension($example, $dimension);
        $this->getEntityManager()->persist($exampleDimension);

        return $exampleDimension;
    }
}
