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
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
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
        ContentLoaderInterface $contentLoader,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        LegacyPropertyFactory $propertyFactory
    ): ContentRouteDefaultsProvider {
        return new ContentRouteDefaultsProvider(
            $entityManager, $contentLoader, $structureMetadataFactory, $propertyFactory
        );
    }

    public function testSupports(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);

        $this->assertTrue($contentRouteDefaultsProvider->supports(\get_class($content->reveal())));
        $this->assertFalse($contentRouteDefaultsProvider->supports(\stdClass::class));
    }

    public function testIsPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $contentView = $this->prophesize(TemplateInterface::class);
        $contentView->willImplement(ContentViewInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load(
            $content->reveal(),
            ['locale' => 'en', 'stage' => 'draft']
        )->willReturn($contentView->reveal());

        $this->assertTrue($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntityReturnNoneTemplate(): void
    {
        $contentView = $this->prophesize(ContentViewInterface::class);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'Expected to get "%s" from ContentLoader but "%s" given.',
            TemplateInterface::class,
            \get_class($contentView->reveal())
        ));

        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load(
            $content->reveal(),
            ['locale' => 'en', 'stage' => 'draft']
        )->willReturn($contentView->reveal());

        $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
    }

    public function testIsPublishedNotExists(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
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

        $contentLoader->load(Argument::cetera())->shouldNotBeCalled();

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testIsNotPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load($content->reveal(), ['locale' => 'en', 'stage' => 'draft'])
            ->will(function ($arguments) {
                throw new ContentNotFoundException($arguments[0], $arguments[1]);
            });

        $this->assertFalse($contentRouteDefaultsProvider->isPublished(Example::class, '123-123-123', 'en'));
    }

    public function testGetByEntity(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $contentView = $this->prophesize(TemplateInterface::class);
        $contentView->willImplement(ContentViewInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load($content->reveal(), ['locale' => 'en', 'stage' => 'draft'])
            ->willReturn($contentView->reveal());

        $contentView->getTemplateType()->willReturn('example');
        $contentView->getTemplateKey()->willReturn('default');

        $metadata = $this->prophesize(StructureMetadata::class);
        $metadata->getView()->willReturn('default');
        $metadata->getController()->willReturn('App\Controller\TestController:testAction');

        $structureMetadataFactory->getStructureMetadata('example', 'default')->willReturn($metadata->reveal());

        $result = $contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en');
        $this->assertSame($contentView->reveal(), $result['object']);
        $this->assertSame('default', $result['view']);
        $this->assertInstanceOf(ContentStructureBridge::class, $result['structure']);
        $this->assertSame('App\Controller\TestController:testAction', $result['_controller']);
    }

    public function testGetByEntityNotPublished(): void
    {
        $entityManager = $this->prophesize(EntityManagerInterface::class);
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load($content->reveal(), ['locale' => 'en', 'stage' => 'draft'])
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
        $contentLoader = $this->prophesize(ContentLoaderInterface::class);
        $structureMetadataFactory = $this->prophesize(StructureMetadataFactoryInterface::class);
        $propertyFactory = $this->prophesize(LegacyPropertyFactory::class);

        $contentRouteDefaultsProvider = $this->getContentRouteDefaultsProvider(
            $entityManager->reveal(),
            $contentLoader->reveal(),
            $structureMetadataFactory->reveal(),
            $propertyFactory->reveal()
        );

        $content = $this->prophesize(ContentInterface::class);
        $contentView = $this->prophesize(TemplateInterface::class);
        $contentView->willImplement(ContentViewInterface::class);

        $queryBuilder = $this->prophesize(QueryBuilder::class);
        $query = $this->prophesize(AbstractQuery::class);

        $entityManager->createQueryBuilder()->willReturn($queryBuilder->reveal());
        $queryBuilder->select('entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->from(Example::class, 'entity')->willReturn($queryBuilder->reveal());
        $queryBuilder->where('entity.id = :id')->willReturn($queryBuilder->reveal());
        $queryBuilder->setParameter('id', '123-123-123')->willReturn($queryBuilder->reveal());
        $queryBuilder->getQuery()->willReturn($query);
        $query->getSingleResult()->willReturn($content->reveal());

        $contentLoader->load($content->reveal(), ['locale' => 'en', 'stage' => 'draft'])
            ->willReturn($contentView->reveal());

        $contentView->getTemplateType()->willReturn('example');
        $contentView->getTemplateKey()->willReturn('default');

        $structureMetadataFactory->getStructureMetadata('example', 'default')->willReturn(null);

        $this->assertEmpty($contentRouteDefaultsProvider->getByEntity(Example::class, '123-123-123', 'en'));
    }
}
