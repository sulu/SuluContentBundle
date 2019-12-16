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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route\ContentRouteDefaultsProvider;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route\ContentStructureBridge;
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\Example;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ContentRouteDefaultsProviderTest extends TestCase
{
    protected function getContentRouteDefaultsProvider(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        LegacyPropertyFactory $propertyFactory
    ): ContentRouteDefaultsProvider {
        return new ContentRouteDefaultsProvider(
            $entityManager, $contentResolver, $structureMetadataFactory, $propertyFactory
        );
    }

    public function testSupports(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $this->assertTrue($contentRouteDefaultsProvider->supports(\get_class($contentRichEntity->reveal())));
        $this->assertFalse($contentRouteDefaultsProvider->supports(\stdClass::class));
    }

    public function testIsPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);
        $contentProjection->getDimensionId()->willReturn('123-456');

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findOneBy(['id' => '123-456'])->willReturn(new Dimension('123-456', [
            'locale' => 'en',
            'stage' => 'live',
        ]));
        $entityManager->getRepository(DimensionInterface::class)->willReturn($dimensionRepository->reveal());

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willReturn($contentProjection->reveal());

        $this->assertTrue($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedNoDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willThrow(new ContentNotFoundException($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live']));

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsPublishedWithDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );
        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);
        $contentProjection->getDimensionId()->willReturn('123-456');

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findOneBy(['id' => '123-456'])->willReturn(new Dimension('123-456', [
            'locale' => 'en',
            'stage' => 'live',
        ]));

        $entityManager->getRepository(DimensionInterface::class)->willReturn($dimensionRepository->reveal());
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertTrue($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsNotPublishedWithMissingDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);
        $contentProjection->getDimensionId()->willReturn('123-456');

        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findOneBy(['id' => '123-456'])->willReturn(null);

        $entityManager->getRepository(DimensionInterface::class)->willReturn($dimensionRepository->reveal());
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsNotPublishedWithDimension(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);
        $contentProjection->getDimensionId()->willReturn('123-456');
        $dimensionRepository = $this->prophesize(DimensionRepositoryInterface::class);
        $dimensionRepository->findOneBy(['id' => '123-456'])->willReturn(new Dimension('123-456', [
            'locale' => 'en',
            'stage' => 'draft',
        ]));

        $entityManager->getRepository(DimensionInterface::class)->willReturn($dimensionRepository->reveal());
        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);
        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($contentProjection->reveal())
            ->shouldBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntityReturnNoneTemplate(): void
    {
        $contentProjection = $this->prophesize(ContentProjectionInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected to get "%s" from ContentResolver but "%s" given.',
            TemplateInterface::class,
            \get_class($contentProjection->reveal())
        ));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve(
            $contentRichEntity->reveal(),
            ['locale' => 'en', 'stage' => 'live']
        )->willReturn($contentProjection->reveal());

        $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
    }

    public function testIsPublishedNotExists(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willThrow(new NoResultException());

        $contentResolver->resolve(Argument::cetera())->shouldNotBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsNotPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->will(function ($arguments) {
                throw new ContentNotFoundException($arguments[0], $arguments[1]);
            });

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntity(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($contentProjection->reveal());

        $contentProjection->getTemplateType()->willReturn('example');
        $contentProjection->getTemplateKey()->willReturn('default');

        $metadata = $this->prophesize(StructureMetadata::class);
        $metadata->getView()->willReturn('default');
        $metadata->getController()->willReturn('App\Controller\TestController:testAction');

        $structureMetadataFactory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal());

        $result = $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
        $this->assertSame($contentProjection->reveal(), $result['object']);
        $this->assertSame('default', $result['view']);
        $this->assertInstanceOf(ContentStructureBridge::class, $result['structure']);
        $this->assertSame('App\Controller\TestController:testAction', $result['_controller']);
    }

    public function testGetByEntityNotPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->will(function ($arguments) {
                throw new ContentNotFoundException($arguments[0], $arguments[1]);
            });

        $metadata = $this->prophesize(StructureMetadata::class);
        $metadata->getView()->willReturn('default');
        $metadata->getController()->willReturn('App\Controller\TestController:testAction');

        $structureMetadataFactory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal());

        $this->assertEmpty($contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntityNoMetadata(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentResolver = $this->prophesize(ContentResolverInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentResolver->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $contentRichEntity = $this->prophesize(ContentRichEntityInterface::class);
        $contentProjection = $this->prophesize(TemplateInterface::class);
        $contentProjection->willImplement(ContentProjectionInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($contentRichEntity->reveal());

        $contentResolver->resolve($contentRichEntity->reveal(), ['locale' => 'en', 'stage' => 'live'])
            ->willReturn($contentProjection->reveal());

        $contentProjection->getTemplateType()->willReturn('example');
        $contentProjection->getTemplateKey()->willReturn('default');

        $structureMetadataFactory->getStructureMetadata('example', 'default')->willReturn(null);

        $this->assertEmpty($contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en'));
    }
}
