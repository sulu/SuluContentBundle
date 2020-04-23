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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\ContentDataMapperInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;

class ContentObjectProvider implements PreviewObjectProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var ContentDataMapperInterface
     */
    private $contentDataMapper;

    /**
     * @var string
     */
    private $entityClass;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentDataMapperInterface $contentDataMapper,
        string $entityClass
    ) {
        $this->entityManager = $entityManager;
        $this->contentResolver = $contentResolver;
        $this->contentDataMapper = $contentDataMapper;
        $this->entityClass = $entityClass;
    }

    /**
     * @param string $id
     * @param string $locale
     *
     * @return DimensionContentInterface|null
     */
    public function getObject($id, $locale)
    {
        try {
            /** @var ContentRichEntityInterface $contentRichEntity */
            $contentRichEntity = $this->entityManager->createQueryBuilder()
                ->select('entity')
                ->from($this->entityClass, 'entity')
                ->where('entity.id = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }

        return $this->loadProjection($contentRichEntity, $locale);
    }

    /**
     * @param DimensionContentInterface $object
     *
     * @return string
     */
    public function getId($object)
    {
        return $object->getContentId();
    }

    /**
     * @param DimensionContentInterface $object
     * @param string $locale
     * @param mixed[] $data
     */
    public function setValues($object, $locale, array $data): void
    {
        $this->contentDataMapper->map($data, $object, $object);
    }

    /**
     * @param DimensionContentInterface $object
     * @param string $locale
     * @param mixed[] $context
     */
    public function setContext($object, $locale, array $context): DimensionContentInterface
    {
        if ($object instanceof TemplateInterface) {
            if (\array_key_exists('template', $context)) {
                $object->setTemplateKey($context['template']);
            }
        }

        return $object;
    }

    /**
     * @param ContentProjectionInterface $object
     *
     * @return string
     */
    public function serialize($object)
    {
        return json_encode([
            'id' => $object->getContentId(),
            'locale' => $object->getDimension()->getLocale(),
        ]) ?: '[]';
    }

    /**
     * @param string $serializedObject
     * @param string $objectClass
     *
     * @return mixed
     */
    public function deserialize($serializedObject, $objectClass)
    {
        $data = json_decode($serializedObject, true);

        $id = $data['id'] ?? null;
        $locale = $data['locale'] ?? null;

        if (!$id || !$locale) {
            return null;
        }

        return $this->getObject($id, $locale);
    }

    protected function loadProjection(ContentRichEntityInterface $contentRichEntity, string $locale): ?DimensionContentInterface
    {
        try {
            $contentProjection = $this->contentResolver->resolve(
                $contentRichEntity,
                [
                    'locale' => $locale,
                    'stage' => DimensionInterface::STAGE_DRAFT,
                ]
            );

            return $contentProjection;
        } catch (ContentNotFoundException $exception) {
            return null;
        }
    }
}
