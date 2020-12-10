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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionCollection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;

class DimensionContentRepositoryTest extends BaseTestCase
{
    protected function setUp(): void
    {
        self::bootKernel();
        self::purgeDatabase();
    }

    public function createContentDimensionRepository(): DimensionContentRepositoryInterface
    {
        return self::$container->get('sulu_content.dimension_content_repository');
    }

    public function testLoadExistAll(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);
        $dimension3 = $this->createDimension('789-456', ['locale' => 'en']);

        $contentRichEntity = $this->createContentRichEntity();
        $dimensionContent1 = $this->createContentDimension($contentRichEntity, $dimension1);
        $dimensionContent2 = $this->createContentDimension($contentRichEntity, $dimension2);
        $this->createContentDimension($contentRichEntity, $dimension3);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $dimensionContentRepository = $this->createContentDimensionRepository();
        $dimensionContentCollection = $dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(2, $dimensionContentCollection);

        $this->assertSame([
            $dimensionContent1->getId(),
            $dimensionContent2->getId(),
        ], array_map(function (ExampleDimensionContent $dimensionContent) {
            return $dimensionContent->getId();
        }, iterator_to_array($dimensionContentCollection)));
    }

    public function testLoadOneNotExist(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);

        $contentRichEntity = $this->createContentRichEntity();
        $dimensionContent1 = $this->createContentDimension($contentRichEntity, $dimension1);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $dimensionContentRepository = $this->createContentDimensionRepository();
        $dimensionContentCollection = $dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(1, $dimensionContentCollection);

        $this->assertSame([
            $dimensionContent1->getId(),
        ], array_map(function (ExampleDimensionContent $dimensionContent) {
            return $dimensionContent->getId();
        }, iterator_to_array($dimensionContentCollection)));
    }

    public function testLoadExistOrderedDifferent(): void
    {
        $attributes = ['locale' => 'de'];

        $dimension1 = $this->createDimension('123-456', []);
        $dimension2 = $this->createDimension('456-789', ['locale' => 'de']);

        $contentRichEntity = $this->createContentRichEntity();
        // First create the dimension 2 to test if its still the last dimension
        $dimensionContent2 = $this->createContentDimension($contentRichEntity, $dimension2);
        $dimensionContent1 = $this->createContentDimension($contentRichEntity, $dimension1);

        $dimensionCollection = new DimensionCollection($attributes, [
            $dimension1,
            $dimension2,
        ]);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $dimensionContentRepository = $this->createContentDimensionRepository();
        $dimensionContentCollection = $dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        $this->assertCount(2, $dimensionContentCollection);

        $this->assertSame([
            $dimensionContent1->getId(),
            $dimensionContent2->getId(), // Dimension 2 should be the last one in this case
        ], array_map(function (ExampleDimensionContent $dimensionContent) {
            return $dimensionContent->getId();
        }, iterator_to_array($dimensionContentCollection)));
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

    private function createContentDimension(Example $example, DimensionInterface $dimension): ExampleDimensionContent
    {
        $exampleDimension = new ExampleDimensionContent($example, $dimension);
        $this->getEntityManager()->persist($exampleDimension);

        return $exampleDimension;
    }
}
