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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Route;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\ContentStructureBridgeFactory;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\StructureMetadataNotFoundException;
use Sulu\Bundle\HttpCacheBundle\CacheLifetime\CacheLifetimeResolverInterface;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ContentRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ContentResolverInterface
     */
    protected $contentResolver;

    /**
     * @var ContentStructureBridgeFactory
     */
    protected $contentStructureBridgeFactory;

    /**
     * @var CacheLifetimeResolverInterface
     */
    private $cacheLifetimeResolver;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentStructureBridgeFactory $contentStructureBridgeFactory,
        CacheLifetimeResolverInterface $cacheLifetimeResolver
    ) {
        $this->entityManager = $entityManager;
        $this->contentResolver = $contentResolver;
        $this->contentStructureBridgeFactory = $contentStructureBridgeFactory;
        $this->cacheLifetimeResolver = $cacheLifetimeResolver;
    }

    /**
     * @param string $entityClass
     * @param string $id
     * @param string $locale
     * @param DimensionContentInterface|null $object
     *
     * @return mixed[]
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        $entity = $object ?: $this->loadEntity($entityClass, $id, $locale);
        if (!$entity) {
            // return empty array which will lead to a 404 response
            return [];
        }

        if (!$entity instanceof TemplateInterface) {
            throw new \RuntimeException(sprintf('Expected to get "%s" from ContentResolver but "%s" given.', TemplateInterface::class, \get_class($entity)));
        }

        try {
            $structureBridge = $this->contentStructureBridgeFactory->getBridge($entity, $id, $locale);
        } catch (StructureMetadataNotFoundException $exception) {
            // return empty array which will lead to a 404 response
            return [];
        }

        return [
            'object' => $entity,
            'view' => $structureBridge->getView(),
            'structure' => $structureBridge,
            '_controller' => $structureBridge->getController(),
            '_cacheLifetime' => $this->getCacheLifetime($structureBridge->getStructure()),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        $entity = $this->loadEntity($entityClass, $id, $locale);

        if ($entity instanceof DimensionContentInterface) {
            return DimensionContentInterface::STAGE_LIVE === $entity->getStage() && $locale === $entity->getLocale();
        }

        return null !== $entity;
    }

    public function supports($entityClass)
    {
        // need to support DimensionContentInterface::class because of the ContentObjectProvider::deserialize() method
        return is_a($entityClass, ContentRichEntityInterface::class, true)
            || is_a($entityClass, DimensionContentInterface::class, true);
    }

    protected function loadEntity(string $entityClass, string $id, string $locale): ?TemplateInterface
    {
        try {
            /** @var ContentRichEntityInterface $contentRichEntity */
            $contentRichEntity = $this->entityManager->createQueryBuilder()
                ->select('entity')
                ->from($entityClass, 'entity')
                ->where('entity.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }

        try {
            // TODO:
            //      to support other dimension attributes here
            //      we should maybe get dimension Attributes from request attributes set by a request listener
            //      e.g. $request->attributes->get('_sulu_content_dimension_attributes');
            $resolvedDimensionContent = $this->contentResolver->resolve(
                $contentRichEntity,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_LIVE,
                ]
            );

            if (!$resolvedDimensionContent instanceof TemplateInterface) {
                throw new \RuntimeException(sprintf('Expected to get "%s" from ContentResolver but "%s" given.', TemplateInterface::class, \get_class($resolvedDimensionContent)));
            }

            return $resolvedDimensionContent;
        } catch (ContentNotFoundException $exception) {
            return null;
        }
    }

    private function getCacheLifetime(StructureMetadata $metadata): ?int
    {
        $cacheLifetime = $metadata->getCacheLifetime();
        if (!$cacheLifetime) {
            // TODO FIXME add test case for this
            return null; // @codeCoverageIgnore
        }

        if (!\is_array($cacheLifetime)
            || !isset($cacheLifetime['type'])
            || !isset($cacheLifetime['value'])
            || !$this->cacheLifetimeResolver->supports($cacheLifetime['type'], $cacheLifetime['value'])
        ) {
            // TODO FIXME add test case for this
            throw new \InvalidArgumentException(sprintf('Invalid cachelifetime in route default provider: %s', var_export($cacheLifetime, true))); // @codeCoverageIgnore
        }

        return $this->cacheLifetimeResolver->resolve($cacheLifetime['type'], $cacheLifetime['value']);
    }
}
