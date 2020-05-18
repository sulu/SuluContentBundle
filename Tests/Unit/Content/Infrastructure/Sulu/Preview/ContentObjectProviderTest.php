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
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;

class ContentObjectProviderTest extends TestCase
{
    /**
     * @var ObjectProphecy|EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ObjectProphecy|ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var ObjectProphecy|ContentDataMapperInterface
     */
    private $contentDataMapper;

    /**
     * @var ContentObjectProvider
     */
    private $contentObjectProvider;

    public function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->contentResolver = $this->prophesize(ContentResolverInterface::class);
        $this->contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);

        $this->contentObjectProvider = new ContentObjectProvider(
            $this->entityManager->reveal(),
            $this->contentResolver->reveal(),
            $this->contentDataMapper->reveal(),
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

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willReturn($resolvedContent->reveal())->shouldBeCalledTimes(1);

        $result = $this->contentObjectProvider->getObject((string) $id, $locale);

        $this->assertSame($resolvedContent->reveal(), $result);
    }

    public function testGetNonExistingObject(int $id = 1, string $locale = 'de'): void
    {
        $this->entityManager->createQueryBuilder()->willThrow(NoResultException::class)->shouldBeCalledTimes(1);

        $result = $this->contentObjectProvider->getObject((string) $id, $locale);

        $this->assertNull($result);
    }

    public function testGetObjectContentNotFound(int $id = 1, string $locale = 'de'): void
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

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willThrow(ContentNotFoundException::class)->shouldBeCalledTimes(1);

        $result = $this->contentObjectProvider->getObject((string) $id, $locale);

        $this->assertNull($result);
    }

    public function testGetId(int $id = 1): void
    {
        $resolvedContent = $this->prophesize(DimensionContentInterface::class);

        $resolvedContent->getContentId()->willReturn($id);

        $actualId = (string) $this->contentObjectProvider->getId($resolvedContent->reveal());

        $this->assertSame((string) $id, $actualId);
    }

    /**
     * @param mixed[] $data
     */
    public function testSetValues(
        string $locale = 'de',
        array $data = [
            'title' => 'Title',
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keywords',
            'seoCanonicalUrl' => 'Seo Canonical Url',
            'seoNoIndex' => true,
            'seoNoFollow' => true,
            'seoHideInSitemap' => true,
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptTags' => ['foo', 'bar'],
            'excerptCategories' => [1, 2],
            'excerptImage' => ['id' => 3],
            'excerptIcon' => ['id' => 4],
        ]
    ): void {
        $resolvedContent = (new class() implements DimensionContentInterface, TemplateInterface, SeoInterface, ExcerptInterface {
            use v;
            use TemplateTrait;
            use SeoTrait;
            use ExcerptTrait;

            public static function getTemplateType(): string
            {
                return 'example';
            }
        });

        $this->contentObjectProvider->setValues($resolvedContent, $locale, $data);

        $this->contentDataMapper->map($data, $resolvedContent, $resolvedContent)->shouldBeCalledTimes(1);
    }

    /**
     * @param mixed[] $context
     */
    public function testSetContext(string $locale = 'de', array $context = ['template' => 'overview']): void
    {
        $resolvedContent = (new class() implements DimensionContentInterface, TemplateInterface {
            use DimensionContentTrait;
            use TemplateTrait;

            public function getContentId()
            {
                return 1;
            }

            public static function getTemplateType(): string
            {
                return 'example';
            }
        });

        $this->contentObjectProvider->setContext($resolvedContent, $locale, $context);

        $this->assertSame($context['template'], $resolvedContent->getTemplateKey());
    }

    public function testSerialize(): void
    {
        $resolvedContent = $this->prophesize(DimensionContentInterface::class);
        $resolvedContent->getContentId()->willReturn('123-456');

        $dimension = $this->prophesize(DimensionInterface::class);
        $dimension->getLocale()->willReturn('en');
        $resolvedContent->getDimension()->willReturn($dimension->reveal());

        $serializedObject = json_encode([
            'id' => '123-456',
            'locale' => 'en',
        ]);

        $result = $this->contentObjectProvider->serialize($resolvedContent->reveal());

        $this->assertSame($serializedObject, $result);
    }

    public function testDeserialize(): void
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

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function () {
            return func_get_arg(\func_num_args() - 2);
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $resolvedContent = $this->prophesize(DimensionContentInterface::class);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willReturn($resolvedContent->reveal())->shouldBeCalledTimes(1);

        $serializedObject = json_encode([
            'id' => '123-456',
            'locale' => 'en',
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertSame($resolvedContent->reveal(), $result);
    }

    public function testDeserializeIdNull(): void
    {
        $serializedObject = json_encode([
            'id' => null,
            'locale' => 'en',
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertNull($result);
    }

    public function testDeserializeLocaleNull(): void
    {
        $serializedObject = json_encode([
            'id' => '123-456',
            'locale' => null,
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertNull($result);
    }
}
