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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Doctrine;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine\MetadataLoader;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimension;

class MetadataLoaderTest extends TestCase
{
    protected function getMetadataLoader(): MetadataLoader
    {
        return new MetadataLoader();
    }

    /**
     * @param string[] $interfaces
     * @param bool[] $fields
     * @param bool[] $associations
     *
     * @dataProvider dataProvider
     */
    public function testInvalidMetadata(array $interfaces, array $fields, array $associations): void
    {
        $metadataLoader = $this->getMetadataLoader();
        $reflectionClass = $this->prophesize(\ReflectionClass::class);

        $reflectionClass->implementsInterface(ContentViewInterface::class)->willReturn(\in_array(ContentViewInterface::class, $interfaces, true));
        $reflectionClass->implementsInterface(ContentDimensionInterface::class)->willReturn(\in_array(ContentDimensionInterface::class, $interfaces, true));
        $reflectionClass->implementsInterface(SeoInterface::class)->willReturn(\in_array(SeoInterface::class, $interfaces, true));
        $reflectionClass->implementsInterface(ExcerptInterface::class)->willReturn(\in_array(ExcerptInterface::class, $interfaces, true));
        $reflectionClass->implementsInterface(TemplateInterface::class)->willReturn(\in_array(TemplateInterface::class, $interfaces, true));

        foreach ($interfaces as $interface) {
            $reflectionClass->implementsInterface($interface)->willReturn(true);
        }

        $classMetadata = $this->prophesize(ClassMetadata::class);
        $classMetadata->getReflectionClass()->willReturn($reflectionClass->reveal());
        $classMetadata->getTableName()->willReturn('test_example');
        $classMetadata->getName()->willReturn(ExampleDimension::class);

        foreach ($fields as $field => $exist) {
            $classMetadata->hasField($field)->willReturn($exist);
            $classMetadata->mapField(Argument::that(function (array $mapping) use ($field) {
                return $mapping['fieldName'] === $field;
            }))->shouldBeCalledTimes($exist ? 0 : 1);
        }

        foreach ($associations as $association => $exist) {
            $classMetadata->hasAssociation($association)->willReturn($exist);
            $classMetadata->mapManyToMany(Argument::that(function (array $mapping) use ($association) {
                return $mapping['fieldName'] === $association;
            }))->shouldBeCalledTimes($exist ? 0 : 1);
        }

        $configuration = $this->prophesize(Configuration::class);
        $configuration->getNamingStrategy()->willReturn(new UnderscoreNamingStrategy());
        $entityManager = $this->prophesize(EntityManager::class);
        $entityManager->getConfiguration()->willReturn($configuration->reveal());

        $metadataLoader->loadClassMetadata(
            new LoadClassMetadataEventArgs($classMetadata->reveal(), $entityManager->reveal())
        );
    }

    public function dataProvider(): \Generator
    {
        yield [
            [
                ContentDimensionInterface::class,
            ],
            [
                'dimensionId' => false,
            ],
            [
            ],
        ];

        yield [
            [
                ContentViewInterface::class,
            ],
            [
                'dimensionId' => false,
            ],
            [
            ],
        ];

        yield [
            [
                ExcerptInterface::class,
            ],
            [
                'excerptTitle' => false,
                'excerptDescription' => false,
                'excerptMore' => false,
                'excerptImage' => false,
                'excerptIcon' => false,
            ],
            [
                'excerptTags' => false,
                'excerptCategories' => false,
            ],
        ];

        yield [
            [
                SeoInterface::class,
            ],
            [
                'seoTitle' => false,
                'seoDescription' => false,
                'seoKeywords' => false,
                'seoCanonicalUrl' => false,
                'seoNoIndex' => false,
                'seoNoFollow' => false,
                'seoHideInSitemap' => false,
            ],
            [],
        ];

        yield [
            [
                TemplateInterface::class,
            ],
            [
                'templateKey' => false,
                'templateData' => false,
            ],
            [],
        ];

        yield [
            [
                ExcerptInterface::class,
            ],
            [
                'excerptTitle' => true,
                'excerptDescription' => false,
                'excerptMore' => false,
                'excerptImage' => false,
                'excerptIcon' => false,
            ],
            [
                'excerptTags' => true,
                'excerptCategories' => false,
            ],
        ];
    }
}
