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
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Webmozart\Assert\Assert;

class ContentObjectProvider implements PreviewObjectProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $structureMetadataFactory;

    /**
     * @var LegacyPropertyFactory
     */
    private $propertyFactory;

    /**
     * @var ContentResolverInterface
     */
    private $contentResolver;

    /**
     * @var TagFactoryInterface
     */
    private $tagFactory;

    /**
     * @var CategoryFactoryInterface
     */
    private $categoryFactory;

    /**
     * @var string
     */
    private $entityClass;

    public function __construct(
        EntityManagerInterface $entityManager,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        LegacyPropertyFactory $propertyFactory,
        ContentResolverInterface $contentResolver,
        TagFactoryInterface $tagFactory,
        CategoryFactoryInterface $categoryFactory,
        string $entityClass
    ) {
        $this->entityManager = $entityManager;
        $this->structureMetadataFactory = $structureMetadataFactory;
        $this->propertyFactory = $propertyFactory;
        $this->contentResolver = $contentResolver;
        $this->tagFactory = $tagFactory;
        $this->categoryFactory = $categoryFactory;
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     *
     * @param ContentProjectionInterface $object
     */
    public function getId($object)
    {
        return $object->getId();
    }

    /**
     * {@inheritdoc}
     *
     * @param ContentProjectionInterface $object
     */
    public function setValues($object, $locale, array $data)
    {
        if ($object instanceof SeoInterface) {
            $this->setSeoData($object, $data);
        }

        if ($object instanceof ExcerptInterface) {
            $this->setExcerptData($object, $data);
        }

        if ($object instanceof TemplateInterface) {
            $this->setTemplateData($object, $data);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function setSeoData(SeoInterface $object, array &$data): void
    {
        $seoTitle = 'seoTitle';
        if (\array_key_exists($seoTitle, $data)) {
            $value = $data[$seoTitle];

            Assert::nullOrString($value);

            $object->setSeoTitle($value);

            unset($data[$seoTitle]);
        }

        $seoDescription = 'seoDescription';
        if (\array_key_exists($seoDescription, $data)) {
            $value = $data[$seoDescription];

            Assert::nullOrString($value);

            $object->setSeoDescription($value);

            unset($data[$seoDescription]);
        }

        $seoKeywords = 'seoKeywords';
        if (\array_key_exists($seoKeywords, $data)) {
            $value = $data[$seoKeywords];

            Assert::nullOrString($value);

            $object->setSeoKeywords($value);

            unset($data[$seoKeywords]);
        }

        $seoCanonicalUrl = 'seoCanonicalUrl';
        if (\array_key_exists($seoCanonicalUrl, $data)) {
            $value = $data[$seoCanonicalUrl];

            Assert::nullOrString($value);

            $object->setSeoCanonicalUrl($value);

            unset($data[$seoCanonicalUrl]);
        }

        $seoNoIndex = 'seoNoIndex';
        if (\array_key_exists($seoNoIndex, $data)) {
            $value = $data[$seoNoIndex];

            Assert::boolean($value);

            $object->setSeoNoIndex($value);

            unset($data[$seoNoIndex]);
        }

        $seoNoFollow = 'seoNoFollow';
        if (\array_key_exists($seoNoFollow, $data)) {
            $value = $data[$seoNoFollow];

            Assert::boolean($value);

            $object->setSeoNoFollow($value);

            unset($data[$seoNoFollow]);
        }

        $seoHideInSitemap = 'seoHideInSitemap';
        if (\array_key_exists($seoHideInSitemap, $data)) {
            $value = $data[$seoHideInSitemap];

            Assert::boolean($value);

            $object->setSeoHideInSitemap($value);

            unset($data[$seoHideInSitemap]);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function setExcerptData(ExcerptInterface $object, array &$data): void
    {
        $excerptTitle = 'excerptTitle';
        if (\array_key_exists($excerptTitle, $data)) {
            $value = $data[$excerptTitle];

            Assert::nullOrString($value);

            $object->setExcerptTitle($value);

            unset($data[$excerptTitle]);
        }

        $excerptDescription = 'excerptDescription';
        if (\array_key_exists($excerptDescription, $data)) {
            $value = $data[$excerptDescription];

            Assert::nullOrString($value);

            $object->setExcerptDescription($value);

            unset($data[$excerptDescription]);
        }

        $excerptMore = 'excerptMore';
        if (\array_key_exists($excerptMore, $data)) {
            $value = $data[$excerptMore];

            Assert::nullOrString($value);

            $object->setExcerptMore($value);

            unset($data[$excerptMore]);
        }

        $excerptCategories = 'excerptCategories';
        if (\array_key_exists($excerptCategories, $data)) {
            $value = $data[$excerptCategories];

            Assert::isArray($value);

            $categories = $this->categoryFactory->create($value);

            $object->setExcerptCategories($categories);

            unset($data[$excerptCategories]);
        }

        $excerptTags = 'excerptTags';
        if (\array_key_exists($excerptTags, $data)) {
            $value = $data[$excerptTags];

            Assert::isArray($value);

            $tags = $this->tagFactory->create($value);

            $object->setExcerptTags($tags);

            unset($data[$excerptTags]);
        }

        $excerptImage = 'excerptImage';
        if (\array_key_exists($excerptImage, $data)) {
            $value = $data[$excerptImage];

            Assert::nullOrIsArray($value);

            $object->setExcerptImage($value);

            unset($data[$excerptImage]);
        }

        $excerptIcon = 'excerptIcon';
        if (\array_key_exists($excerptIcon, $data)) {
            $value = $data[$excerptIcon];

            Assert::nullOrIsArray($value);

            $object->setExcerptIcon($value);

            unset($data[$excerptIcon]);
        }
    }

    /**
     * @param mixed[] $data
     */
    private function setTemplateData(TemplateInterface $object, array &$data): void
    {
        $object->setTemplateData($data);
    }

    /**
     * {@inheritdoc}
     *
     * @param ContentProjectionInterface $object
     */
    public function setContext($object, $locale, array $context)
    {
        if ($object instanceof TemplateInterface) {
            if (\array_key_exists('template', $context)) {
                $object->setTemplateKey($context['template']);
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     *
     * @param ContentProjectionInterface $object
     */
    public function serialize($object)
    {
        return serialize($object);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($serializedObject, $objectClass)
    {
        return unserialize($serializedObject);
    }

    protected function loadProjection(ContentRichEntityInterface $contentRichEntity, string $locale): ?ContentProjectionInterface
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
