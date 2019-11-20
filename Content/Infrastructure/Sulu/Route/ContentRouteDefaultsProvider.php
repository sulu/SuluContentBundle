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
use Sulu\Bundle\ContentBundle\Content\Application\Message\LoadContentViewMessage;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;
use Sulu\Bundle\RouteBundle\Routing\Defaults\RouteDefaultsProviderInterface;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class ContentRouteDefaultsProvider implements RouteDefaultsProviderInterface
{
    use HandleTrait;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var StructureMetadataFactoryInterface
     */
    protected $structureMetadataFactory;

    /**
     * @var LegacyPropertyFactory
     */
    private $propertyFactory;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageBusInterface $messageBus,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        LegacyPropertyFactory $propertyFactory
    ) {
        $this->entityManager = $entityManager;
        $this->messageBus = $messageBus;
        $this->structureMetadataFactory = $structureMetadataFactory;
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * @param TemplateInterface|null $object
     */
    public function getByEntity($entityClass, $id, $locale, $object = null)
    {
        $entity = $object ?: $this->loadEntity($entityClass, $id, $locale);
        if (!$entity) {
            return [];
        }

        $metadata = $this->structureMetadataFactory->getStructureMetadata(
            $entity->getTemplateType(),
            $entity->getTemplateKey()
        );
        if (!$metadata) {
            return [];
        }

        $structure = new ContentStructureBridge($metadata, $this->propertyFactory, $entity, $id, $locale);

        return [
            'object' => $entity,
            'view' => $metadata->getView(),
            'structure' => $structure,
            '_controller' => $metadata->getController(),
        ];
    }

    public function isPublished($entityClass, $id, $locale)
    {
        $entity = $this->loadEntity($entityClass, $id, $locale);

        if ($entity instanceof ContentViewInterface) {
            $dimensionId = $entity->getDimensionId();

            /** @var DimensionRepositoryInterface $dimensionRepository */
            $dimensionRepository = $this->entityManager->getRepository(DimensionInterface::class);
            $dimension = $dimensionRepository->findOneBy(['id' => $dimensionId]);

            if (!$dimension) {
                // Return false if dimension does not longer exists
                return false;
            }

            return DimensionInterface::STAGE_LIVE === $dimension->getStage();
        }

        return null !== $entity;
    }

    public function supports($entityClass)
    {
        return is_a($entityClass, ContentInterface::class, true);
    }

    private function loadEntity(string $entityClass, string $id, string $locale): ?TemplateInterface
    {
        try {
            /** @var ContentInterface $content */
            $content = $this->entityManager->createQueryBuilder()
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
            return $this->handle(
                // TODO to support other dimension attributes here
                // TODO we should maybe get dimension Attributes form request attributes set by a request listener
                // TODO e.g. $request->attributes->get('_sulu_content_dimension_attributes');
                new LoadContentViewMessage($content, [
                    'locale' => $locale,
                    'stage' => DimensionInterface::STAGE_LIVE,
                ])
            );
        } catch (HandlerFailedException $exception) {
            return null;
        }
    }
}
