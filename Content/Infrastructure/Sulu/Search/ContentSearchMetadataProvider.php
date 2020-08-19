<?php

declare(strict_types=1);

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Search;

use Doctrine\ORM\EntityManagerInterface;
use Massive\Bundle\SearchBundle\Search\Document;
use Massive\Bundle\SearchBundle\Search\Factory;
use Massive\Bundle\SearchBundle\Search\Metadata\ClassMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\ComplexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\Field\Expression;
use Massive\Bundle\SearchBundle\Search\Metadata\IndexMetadata;
use Massive\Bundle\SearchBundle\Search\Metadata\ProviderInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Component\Content\Metadata\BlockMetadata;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;
use Sulu\Component\Content\Metadata\PropertyMetadata;
use Sulu\Component\Content\Metadata\StructureMetadata;

class ContentSearchMetadataProvider implements ProviderInterface
{
    const SEARCH_FIELD_TAG = 'sulu.search.field';
    const FIELD_TEMPLATE_KEY = '_template_key';
    const EXCERPT_FIELDS = [
        'excerptTitle',
        'excerptMore',
        'excerptDescription',
    ];
    const SEO_FIELDS = [
        'seoTitle',
        'seoDescription',
        'seoKeywords',
        'seoCanonicalUrl',
    ];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Factory
     */
    private $searchMetadataFactory;

    /**
     * @var StructureMetadataFactoryInterface
     */
    private $structureFactory;

    /**
     * @var class-string<ContentRichEntityInterface>
     */
    private $contentRichEntityClass;

    /**
     * @var class-string<DimensionContentInterface>|null
     */
    private $dimensionContentClass = null;

    public function __construct(
        EntityManagerInterface $entityManager,
        Factory $searchMetadataFactory,
        StructureMetadataFactoryInterface $structureFactory,
        string $contentRichEntityClass
    ) {
        $this->entityManager = $entityManager;
        $this->searchMetadataFactory = $searchMetadataFactory;
        $this->structureFactory = $structureFactory;
        $this->contentRichEntityClass = $contentRichEntityClass;
    }

    public function getMetadataForObject($object)
    {
        $dimensionContentClass = $this->getDimensionContentClass();

        if (!is_a($object, $dimensionContentClass, true)
            || !$object instanceof DimensionContentInterface
            || !$object instanceof TemplateInterface) {
            return null;
        }

        if (!$object->isMerged()) {
            return null;
        }

        $structureMetadata = $this->structureFactory->getStructureMetadata(
            $object->getTemplateType(),
            $object->getTemplateKey()
        );

        if (!$structureMetadata) {
            return null;
        }

        return $this->getMetadata($dimensionContentClass, $structureMetadata);
    }

    public function getAllMetadata()
    {
        /** @var DimensionContentInterface&string $dimensionContentClass */
        $dimensionContentClass = $this->getDimensionContentClass();

        $metadata = [];
        foreach ($this->structureFactory->getStructures($dimensionContentClass::getResourceKey()) as $structure) {
            $metadata[] = $this->getMetadata($this->getDimensionContentClass(), $structure);
        }

        return $metadata;
    }

    /**
     * @return ClassMetadata|null
     */
    public function getMetadataForDocument(Document $document)
    {
        /** @var TemplateInterface&string $dimensionContentClass */
        $dimensionContentClass = $this->getDimensionContentClass();

        if (!$document->hasField(self::FIELD_TEMPLATE_KEY) || $dimensionContentClass !== $document->getClass()) {
            return null;
        }

        $structureType = $document->getField(self::FIELD_TEMPLATE_KEY)->getValue();

        $structureMetadata = $this->structureFactory->getStructureMetadata(
            $dimensionContentClass::getTemplateType(),
            $structureType
        );

        if (!$structureMetadata) {
            return null;
        }

        return $this->getMetadata($dimensionContentClass, $structureMetadata);
    }

    protected function getMetadata(string $className, StructureMetadata $structureMetadata): ClassMetadata
    {
        $classMetadata = $this->searchMetadataFactory->createClassMetadata($className);

        $indexMeta = $this->searchMetadataFactory->createIndexMetadata();
        $indexMeta->setIdField($this->searchMetadataFactory->createMetadataField('resourceId'));
        $indexMeta->setLocaleField($this->searchMetadataFactory->createMetadataField('locale'));

        /** @var DimensionContentInterface&string $dimensionContentClass */
        $dimensionContentClass = $this->getDimensionContentClass();

        $indexMeta->setIndexName($this->createIndexNameField($dimensionContentClass::getResourceKey()));

        $indexMeta->addFieldMapping(
            self::FIELD_TEMPLATE_KEY,
            [
                'type' => 'string',
                'stored' => true,
                'indexed' => true,
                'field' => $this->searchMetadataFactory->createMetadataProperty('templateKey'),
            ]
        );

        if (is_a($className, ExcerptInterface::class, true)) {
            foreach (self::EXCERPT_FIELDS as $property) {
                $indexMeta->addFieldMapping(
                    $property,
                    [
                        'field' => $this->searchMetadataFactory->createMetadataProperty($property),
                        'type' => 'string',
                        'aggregate' => true,
                        'indexed' => false,
                    ]
                );
            }
        }

        if (is_a($className, SeoInterface::class, true)) {
            foreach (self::SEO_FIELDS as $property) {
                $indexMeta->addFieldMapping(
                    $property,
                    [
                        'field' => $this->searchMetadataFactory->createMetadataProperty($property),
                        'type' => 'string',
                        'aggregate' => true,
                        'indexed' => false,
                    ]
                );
            }
        }

        foreach ($structureMetadata->getProperties() as $property) {
            if ($property instanceof BlockMetadata) {
                $propertyMapping = new ComplexMetadata();
                foreach ($property->getComponents() as $component) {
                    foreach ($component->getChildren() as $componentProperty) {
                        if (false === $componentProperty->hasTag(self::SEARCH_FIELD_TAG)) {
                            continue;
                        }

                        $tag = $componentProperty->getTag(self::SEARCH_FIELD_TAG);
                        $tagAttributes = $tag['attributes'];

                        if (!isset($tagAttributes['index']) || 'false' !== $tagAttributes['index']) {
                            $propertyMapping->addFieldMapping(
                                $property->getName() . '.' . $componentProperty->getName(),
                                [
                                    'type' => isset($tagAttributes['type']) ? $tagAttributes['type'] : 'string',
                                    'field' => $this->searchMetadataFactory->createMetadataProperty(
                                        '[' . $componentProperty->getName() . ']'
                                    ),
                                    'aggregate' => true,
                                    'indexed' => false,
                                ]
                            );
                        }
                    }
                }

                $indexMeta->addFieldMapping(
                    $property->getName(),
                    [
                        'type' => 'complex',
                        'mapping' => $propertyMapping,
                        'field' => $this->getContentField($property),
                    ]
                );
            } else {
                $this->mapProperty($property, $indexMeta);
            }
        }

        $classMetadata->addIndexMetadata('_default', $indexMeta);

        return $classMetadata;
    }

    private function createIndexNameField(string $indexName): Expression
    {
        $expression = '"' . $indexName . '"';
        $expression .= '~(object.getDimension().getStage() == "' . DimensionInterface::STAGE_LIVE . '" ? "_published" : "")';

        return new Expression($expression);
    }

    private function getContentField(PropertyMetadata $property): Expression
    {
        return $this->searchMetadataFactory->createMetadataExpression(
            sprintf(
                'object.getTemplateData()["%s"]',
                $property->getName()
            )
        );
    }

    private function mapProperty(PropertyMetadata $property, IndexMetadata $metadata): void
    {
        if (!$property->hasTag(self::SEARCH_FIELD_TAG)) {
            return;
        }

        $tag = $property->getTag(self::SEARCH_FIELD_TAG);
        $tagAttributes = $tag['attributes'];

        if (isset($tagAttributes['role'])) {
            switch ($tagAttributes['role']) {
                case 'title':
                    $metadata->setTitleField($this->getContentField($property));
                    $metadata->addFieldMapping(
                        $property->getName(),
                        [
                            'field' => $this->getContentField($property),
                            'type' => 'string',
                            'aggregate' => true,
                            'indexed' => false,
                        ]
                    );
                    break;
                case 'description':
                    $metadata->setDescriptionField($this->getContentField($property));
                    $metadata->addFieldMapping(
                        $property->getName(),
                        [
                            'field' => $this->getContentField($property),
                            'type' => 'string',
                            'aggregate' => true,
                            'indexed' => false,
                        ]
                    );
                    break;
                case 'url':
                    $metadata->setUrlField($this->getContentField($property));
                    $metadata->addFieldMapping(
                        $property->getName(),
                        [
                            'field' => $this->getContentField($property),
                            'type' => 'string',
                            'aggregate' => true,
                            'indexed' => false,
                        ]
                    );
                    break;
                case 'image':
                    $metadata->setImageUrlField($this->getContentField($property));
                    break;
                default:
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Unknown search field role "%s", role must be one of "%s"',
                            $tagAttributes['role'],
                            implode(', ', ['title', 'description', 'image'])
                        )
                    );
            }

            return;
        }

        if (!isset($tagAttributes['index']) || 'false' !== $tagAttributes['index']) {
            $metadata->addFieldMapping(
                $property->getName(),
                [
                    'type' => isset($tagAttributes['type']) ? $tagAttributes['type'] : 'string',
                    'field' => $this->getContentField($property),
                    'aggregate' => true,
                    'indexed' => false,
                ]
            );
        }
    }

    /**
     * @return class-string<DimensionContentInterface>
     */
    private function getDimensionContentClass(): string
    {
        if (null !== $this->dimensionContentClass) {
            return $this->dimensionContentClass;
        }

        $classMetadata = $this->entityManager->getClassMetadata($this->contentRichEntityClass);
        $associationMapping = $classMetadata->getAssociationMapping('dimensionContents');
        $this->dimensionContentClass = $associationMapping['targetEntity'];

        return $this->dimensionContentClass;
    }
}
