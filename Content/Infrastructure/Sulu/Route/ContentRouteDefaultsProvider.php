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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\ContentStructureBridgeFactory;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Structure\StructureMetadataNotFoundException;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;

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

    public function __construct(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentStructureBridgeFactory $contentStructureBridgeFactory
    ) {
        $this->entityManager = $entityManager;
        $this->contentResolver = $contentResolver;
        $this->contentStructureBridgeFactory = $contentStructureBridgeFactory;
    }

    /**
     * @param string $entityClass
     * @param string $id
     * @param string $locale
     * @param ContentProjectionInterface|null $object
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
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        $entity = $this->loadEntity($entityClass, $id, $locale);

        if ($entity instanceof ContentProjectionInterface) {
            $dimensionId = $entity->getDimensionId();
            /** @var DimensionRepositoryInterface $dimensionRepository */
            $dimensionRepository = $this->entityManager->getRepository(DimensionInterface::class);
            $dimension = $dimensionRepository->findOneBy(['id' => $dimensionId]);
            if (!$dimension) {
                // Return false if dimension does not longer exists
                return false;
            }

            return DimensionInterface::STAGE_LIVE === $dimension->getStage() && $locale === $dimension->getLocale();
        }

        return null !== $entity;
    }

    public function supports($entityClass)
    {
        return is_a($entityClass, ContentRichEntityInterface::class, true)
            || is_a($entityClass, ContentProjectionInterface::class, true);
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
            $contentProjection = $this->contentResolver->resolve(
                $contentRichEntity,
                [
                    'locale' => $locale,
                    'stage' => DimensionInterface::STAGE_LIVE,
                ]
            );

            if (!$contentProjection instanceof TemplateInterface) {
                throw new \RuntimeException(sprintf('Expected to get "%s" from ContentResolver but "%s" given.', TemplateInterface::class, \get_class($contentProjection)));
            }

            return $contentProjection;
        } catch (ContentNotFoundException $exception) {
            return null;
        }
    }
}
