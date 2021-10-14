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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Route;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route\ContentRouteDefaultsProvider;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\ContentStructureBridge;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\ContentStructureBridgeFactory;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\StructureMetadataNotFoundException;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ContentRouteDefaultsProviderTest extends TestCase
{
    protected function getContentRouteDefaultsProvider(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentStructureBridgeFactory $contentStructureBridgeFactory,
        CacheLifetimeResolverInterface $cacheLifetimeResolver
    ): ContentRouteDefaultsProvider {
        return new ContentRouteDefaultsProvider(
            $entityManager,
            $contentResolver,
            $contentStructureBridgeFactory,
            $cacheLifetimeResolver
        );
    }

    public function testSupports(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $this->assertTrue($contentRouteDefaultsProvider->supports(\get_class($contentRichEntity->reveal())));
        $this->assertFalse($contentRouteDefaultsProvider->supports(\stdClass::class));
    }

    public function testIsPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $resolvedDimensionContent = $this->prophesize(TemplateInterface::class);
        $resolvedDimensionContent->willImplement(DimensionContentInterface::class);
        $resolvedDimensionContent->getLocale()->willReturn('en');
        $resolvedDimensionContent->getStage()->willReturn('live');

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willReturn($resolvedDimensionContent->reveal());

        $this->assertTrue($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedEntityNotFound(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willThrow(new NoResultException());

        $contentResolver->resolve(Argument::cetera())->shouldNotBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedContentNotFound(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willThrow(new ContentNotFoundException($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live']));

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedWithLocalizedDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $resolvedDimensionContent = $this->prophesize(TemplateInterface::class);
        $resolvedDimensionContent->willImplement(DimensionContentInterface::class);
        $resolvedDimensionContent->getLocale()->willReturn('en');
        $resolvedDimensionContent->getStage()->willReturn('live');

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($resolvedDimensionContent->reveal())
            ->shouldBeCalled();

        $this->assertTrue($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedWithUnlocalizedDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $resolvedDimensionContent = $this->prophesize(TemplateInterface::class);
        $resolvedDimensionContent->willImplement(DimensionContentInterface::class);
        $resolvedDimensionContent->getLocale()->willReturn(null);
        $resolvedDimensionContent->getStage()->willReturn('live');

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($resolvedDimensionContent->reveal())
            ->shouldBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntityReturnNoneTemplate(): void
    {
        $resolvedDimensionContent = $this->prophesize(DimensionContentInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'Expected to get "%s" from ContentResolver but "%s" given.',
            TemplateInterface::class,
            \get_class($resolvedDimensionContent->reveal())
        ));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willReturn($resolvedDimensionContent->reveal());

        $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
    }

    public function testGetByEntityReturnNoneTemplateFromPreview(): void
    {
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(
            'Expected to get "%s" from ContentResolver but "%s" given.',
            TemplateInterface::class,
            \get_class($contentRichEntity->reveal())
        ));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        /** @var DimensionContentInterface */
        $entity = $contentRichEntity->reveal();

        $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en', $entity);
    }

    public function testGetByEntity(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $resolvedDimensionContent = $this->prophesize(TemplateInterface::class);
        $resolvedDimensionContent->willImplement(DimensionContentInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($resolvedDimensionContent->reveal());

        $cacheLifetimeResolver->supports('seconds', 3600)->willReturn(true);
        $cacheLifetimeResolver->resolve('seconds', 3600)->willReturn(3600);

        $structureMetadata = $this->prophesize(StructureMetadata::class);
        $structureMetadata->getCacheLifetime()->willReturn(['value' => 3600, 'type' => 'seconds']);

        $contentStructureBridge = $this->prophesize(ContentStructureBridge::class);
        $contentStructureBridge->getView()->willReturn('default');
        $contentStructureBridge->getController()->willReturn('App\Controller\TestController:testAction');
        $contentStructureBridge->getStructure()->willReturn($structureMetadata->reveal());
        $contentStructureBridgeFactory->getBridge($resolvedDimensionContent->reveal(), '123-123-123', 'en')
            ->willReturn($contentStructureBridge->reveal());

        $result = $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
        $this->assertSame($resolvedDimensionContent->reveal(), $result['object']);
        $this->assertSame('default', $result['view']);
        $this->assertSame($contentStructureBridge->reveal(), $result['structure']);
        $this->assertSame('App\Controller\TestController:testAction', $result['_controller']);
        $this->assertSame(3600, $result['_cacheLifetime']);
    }

    public function testGetByEntityNotPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->will(function($arguments) {
                throw new ContentNotFoundException($arguments[0], $arguments[1]);
            });

        $this->assertEmpty($contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntityStructureMetadataNotFound(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $contentStructureBridgeFactory = $this->prophesize(ContentStructureBridgeFactory::class);
        $cacheLifetimeResolver = $this->prophesize(CacheLifetimeResolverInterface::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $contentStructureBridgeFactory->reveal(),
            $cacheLifetimeResolver->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $resolvedDimensionContent = $this->prophesize(TemplateInterface::class);
        $resolvedDimensionContent->willImplement(DimensionContentInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($resolvedDimensionContent->reveal());

        $contentStructureBridgeFactory->getBridge($resolvedDimensionContent->reveal(), '123-123-123', 'en')
            ->willThrow(StructureMetadataNotFoundException::class);

        $this->assertEmpty($contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en'));
    }
}
