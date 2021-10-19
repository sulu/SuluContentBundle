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
     * @var class-string<ContentRichEntityInterface>
     */
    private $contentRichEntityClass;

    /**
     * @var string|null
     */
    private $securityContext;

    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ContentResolverInterface $contentResolver,
        ContentDataMapperInterface $contentDataMapper,
        string $contentRichEntityClass,
        ?string $securityContext = null
    ) {
        $this->entityManager = $entityManager;
        $this->contentResolver = $contentResolver;
        $this->contentDataMapper = $contentDataMapper;
        $this->contentRichEntityClass = $contentRichEntityClass;
        $this->securityContext = $securityContext;
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
                ->from($this->contentRichEntityClass, 'entity')
                ->where('entity = :id')
                ->setParameter('id', $id)
                ->getQuery()
                ->getSingleResult();
        } catch (NoResultException $exception) {
            return null;
        }

        return $this->resolveContent($contentRichEntity, $locale);
    }

    /**
     * @param DimensionContentInterface $object
     *
     * @return string
     */
    public function getId($object)
    {
        return $object->getResource()->getId();
    }

    /**
     * @param DimensionContentInterface $object
     * @param string $locale
     * @param mixed[] $data
     */
    public function setValues($object, $locale, array $data): void
    {
        $previewDimensionContentCollection = new PreviewDimensionContentCollection($object, $locale);
        $this->contentDataMapper->map(
            $previewDimensionContentCollection,
            $previewDimensionContentCollection->getDimensionAttributes(),
            $data
        );
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
     * @param DimensionContentInterface $object
     *
     * @return string
     */
    public function serialize($object)
    {
        return \json_encode([
            'id' => $object->getResource()->getId(),
            'locale' => $object->getLocale(),
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
        $data = \json_decode($serializedObject, true);

        $id = $data['id'] ?? null;
        $locale = $data['locale'] ?? null;

        if (!$id || !$locale) {
            return null;
        }

        return $this->getObject($id, $locale);
    }

    public function getSecurityContext($id, $locale): ?string
    {
        return $this->securityContext;
    }

    protected function resolveContent(ContentRichEntityInterface $contentRichEntity, string $locale): ?DimensionContentInterface
    {
        try {
            $resolvedDimensionContent = $this->contentResolver->resolve(
                $contentRichEntity,
                [
                    'locale' => $locale,
                    'stage' => DimensionContentInterface::STAGE_DRAFT,
                ]
            );

            if (!$resolvedDimensionContent->getLocale()) {
                // avoid 500 error when ghostLocale is loaded by still use correct locale in serialize method
                $resolvedDimensionContent->setLocale($locale);
            }

            return $resolvedDimensionContent;
        } catch (ContentNotFoundException $exception) {
            return null;
        }
    }
}
