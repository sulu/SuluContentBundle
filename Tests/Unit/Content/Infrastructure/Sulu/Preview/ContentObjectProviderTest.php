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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\ContentObjectProvider;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview\PreviewDimensionContentCollection;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Admin\ExampleAdmin;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleDimensionContent;

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

    protected function setUp(): void
    {
        $this->entityManager = $this->prophesize(EntityManagerInterface::class);
        $this->contentResolver = $this->prophesize(ContentResolverInterface::class);
        $this->contentDataMapper = $this->prophesize(ContentDataMapperInterface::class);

        $this->contentObjectProvider = new ContentObjectProvider(
            $this->entityManager->reveal(),
            $this->contentResolver->reveal(),
            $this->contentDataMapper->reveal(),
            Example::class,
            ExampleAdmin::SECURITY_CONTEXT
        );
    }

    public function testGetObject(int $id = 1, string $locale = 'de'): void
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal())->shouldBeCalledTimes(1);

        $queryBuilder->select(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->from(Argument::type('string'), Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->where(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willReturn($dimensionContent->reveal())->shouldBeCalledTimes(1);

        $result = $this->contentObjectProvider->getObject((string) $id, $locale);

        $this->assertSame($dimensionContent->reveal(), $result);
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

        $queryBuilder->select(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->from(Argument::type('string'), Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->where(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
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
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn($id);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->getResource()->willReturn($contentRichEntity->reveal());

        $actualId = (string) $this->contentObjectProvider->getId($dimensionContent->reveal());

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
        $example = new Example();
        $exampleDimensionContent = new ExampleDimensionContent($example);

        $this->contentObjectProvider->setValues($exampleDimensionContent, $locale, $data);

        $this->contentDataMapper->map(
            Argument::that(
                function(PreviewDimensionContentCollection $dimensionContentCollection) use ($exampleDimensionContent) {
                    return $exampleDimensionContent === $dimensionContentCollection->getDimensionContent([]);
                }
            ),
            ['locale' => 'de', 'stage' => 'draft'],
            $data
        )->shouldBeCalledTimes(1);
    }

    /**
     * @param mixed[] $context
     */
    public function testSetContext(string $locale = 'de', array $context = ['template' => 'overview']): void
    {
        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->willImplement(TemplateInterface::class);

        $this->contentObjectProvider->setContext($dimensionContent->reveal(), $locale, $context);

        $dimensionContent->setTemplateKey($context['template'])->shouldBeCalled();
    }

    public function testSerialize(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentRichEntity->getId()->willReturn('123-456');

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);
        $dimensionContent->getResource()->willReturn($contentRichEntity->reveal());
        $dimensionContent->getLocale()->willReturn('en');
        $dimensionContent->getStage()->willReturn('draft');

        $serializedObject = \json_encode([
            'id' => '123-456',
            'locale' => 'en',
        ]);

        $result = $this->contentObjectProvider->serialize($dimensionContent->reveal());

        $this->assertSame($serializedObject, $result);
    }

    public function testDeserialize(): void
    {
        $queryBuilder = $this->prophesize(QueryBuilder::class);

        $this->entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal())->shouldBeCalledTimes(1);

        $queryBuilder->select(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->from(Argument::type('string'), Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->where(Argument::type('string'))->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $queryBuilder->setParameter(Argument::type('string'), Argument::any())->will(function() {
            return \func_get_arg(max(\func_num_args() - 2, 0));
        })->shouldBeCalledTimes(1);

        $query = $this->prophesize(AbstractQuery::class);

        $queryBuilder->getQuery()->willReturn($query->reveal())->shouldBeCalledTimes(1);

        $entity = $this->prophesize(ContentRichEntityInterface::class);

        $query->getSingleResult()->willReturn($entity->reveal())->shouldBeCalledTimes(1);

        $dimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->contentResolver->resolve(
            $entity->reveal(),
            Argument::type('array')
        )->willReturn($dimensionContent->reveal())->shouldBeCalledTimes(1);

        $serializedObject = \json_encode([
            'id' => '123-456',
            'locale' => 'en',
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertSame($dimensionContent->reveal(), $result);
    }

    public function testDeserializeIdNull(): void
    {
        $serializedObject = \json_encode([
            'id' => null,
            'locale' => 'en',
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertNull($result);
    }

    public function testDeserializeLocaleNull(): void
    {
        $serializedObject = \json_encode([
            'id' => '123-456',
            'locale' => null,
        ]) ?: '';

        $result = $this->contentObjectProvider->deserialize($serializedObject, DimensionContentInterface::class);

        $this->assertNull($result);
    }

    public function testGetSecurityContext(): void
    {
        $this->assertSame(
            ExampleAdmin::SECURITY_CONTEXT,
            $this->contentObjectProvider->getSecurityContext('123-123-123', 'en')
        );
    }
}
