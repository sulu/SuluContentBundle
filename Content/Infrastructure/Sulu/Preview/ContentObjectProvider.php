<?php

declare(strict_types=1);

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Preview;

use App\Event\Domain\Model\EventProjection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Sulu\Bundle\ContentBundle\Content\Application\ContentResolver\ContentResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\PreviewBundle\Preview\Object\PreviewObjectProviderInterface;
use Sulu\Component\Content\Compat\Structure\LegacyPropertyFactory;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

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
     * @var string
     */
    private $entityClass;

    public function __construct(
        EntityManagerInterface $entityManager,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        LegacyPropertyFactory $propertyFactory,
        ContentResolverInterface $contentResolver,
        string $entityClass
    ) {
        $this->entityManager = $entityManager;
        $this->structureMetadataFactory = $structureMetadataFactory;
        $this->propertyFactory = $propertyFactory;
        $this->contentResolver = $contentResolver;
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
            $data = $object->setSeoData($data);
        }

        if ($object instanceof ExcerptInterface) {
            $data = $object->setExcerptData($data);
        }

        if ($object instanceof WorkflowInterface) {
            $data = $object->setWorkflowData($data);
        }

        if ($object instanceof TemplateInterface) {
            $object->setTemplateData($data);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param ContentProjectionInterface $object
     */
    public function setContext($object, $locale, array $context)
    {
        if ($object instanceof TemplateInterface) {
            if (array_key_exists('template', $context)) {
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
