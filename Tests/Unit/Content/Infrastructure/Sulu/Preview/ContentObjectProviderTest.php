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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Preview;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\CategoryBundle\Entity\Category;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\TagBundle\Entity\Tag;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

class ContentObjectProviderTest extends TestCase
{
    private $entityManager;

    private $contentResolver;

    private $tagFactory;

    private $categoryFactory;

    private $contentObjectProvider;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);
        $this->contentResolver = $this->prophesize(ContentResolverInterface::class);
        $this->tagFactory = $this->prophesize(TagFactoryInterface::class);
        $this->categoryFactory = $this->prophesize(CategoryFactoryInterface::class);

        $this->contentObjectProvider = new ContentObjectProvider(
            $this->entityManager->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal(),
            $this->contentResolver->reveal(),
            $this->tagFactory->reveal(),
            $this->categoryFactory->reveal(),
            Example::class
        );
    }

    public function testGetObject(int $id = 1, string $locale = 'de'): void
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal())->shouldBeCalledTimes(1);

        $queryBuilder->select(Argument::type('string'))->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $queryBuilder->from(Argument::type('string'), Argument::type('string'))->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $queryBuilder->where(Argument::type('string'))->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $queryBuilder->setParameter(Argument::type('string'), Argument::type('string'))->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $projection = $this->prophesize(ContentProjectionInterface::class);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willReturn($projection->reveal())->shouldBeCalledTimes(1);

        $result = $this->contentObjectProvider->getObject($id, $locale);

        $this->assertSame($projection->reveal(), $result);
    }

    public function testGetId(int $id = 1): void
    {
        $projection = $this->prophesize(ContentProjectionInterface::class);

        $projection->getId()->willReturn($id);

        $actualId = $this->contentObjectProvider->getId($projection->reveal());

        $this->assertSame($id, $actualId);
    }

    /**
     * @param mixed[] $data
     */
    public function testSetValues(
        string $locale = 'de',
        array $data = [
            'title' => 'Title',
            'seoTitle' => 'Seo Title',
            'excerptTitle' => 'Excerpt Title',
            'excerptTags' => ['foo', 'bar'],
            'excerptCategories' => [1, 2],
        ]
    ): void {
        $this->tagFactory->create(Argument::type('array'))->will(function (array $tags) {
            return array_map(function ($name) {
                $tag = new Tag();
                $tag->setName($name);

                return $tag;
            }, $tags);
        });

        $this->categoryFactory->create(Argument::type('array'))->will(function (array $categories) {
            return array_map(function ($id) {
                $category = new Category();
                $category->setId($id);

                return $category;
            }, $categories);
        });

        $projection = (new class() implements ContentProjectionInterface, TemplateInterface, SeoInterface, ExcerptInterface {
            use ContentProjectionTrait;
            use TemplateTrait;
            use SeoTrait;
            use ExcerptTrait;

            public function getContentId()
            {
                return 1;
            }

            public function getTemplateType(): string
            {
                return 'example';
            }
        });

        $newData = $data;
        $this->contentObjectProvider->setValues($projection, $locale, $newData);

        $this->assertSame('Title', $projection->getTemplateData()['title']);
        $this->assertSame('Seo Title', $projection->getSeoTitle());
        $this->assertSame('Excerpt Title', $projection->getExcerptTitle());
        $this->assertSame($data['excerptTags'], $projection->getExcerptTagNames());
        $this->assertSame($data['excerptCategories'], $projection->getExcerptCategoryIds());
    }

    /**
     * @param mixed[] $context
     */
    public function testSetContext(string $locale = 'de', array $context = ['template' => 'overview']): void
    {
        $projection = (new class() implements ContentProjectionInterface, TemplateInterface {
            use ContentProjectionTrait;
            use TemplateTrait;

            public function getContentId()
            {
                return 1;
            }

            public function getTemplateType(): string
            {
                return 'example';
            }
        });

        $this->contentObjectProvider->setContext($projection, $locale, $context);

        $this->assertSame($context['template'], $projection->getTemplateKey());
    }

    public function testSerialize(): void
    {
        $object = new \stdClass();
        $serializedObject = serialize($object);

        $result = $this->contentObjectProvider->serialize($object);

        $this->assertSame($serializedObject, $result);
    }

    public function testDeserialize(): void
    {
        $object = new \stdClass();
        $serializedObject = serialize($object);

        $deserializedObject = $this->contentObjectProvider->deserialize($serializedObject, \get_class($object));

        $this->assertSame($deserializedObject, $object);
    }
}
