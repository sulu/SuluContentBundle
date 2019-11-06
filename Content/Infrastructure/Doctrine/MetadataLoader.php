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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class MetadataLoader implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        $metadata = $event->getClassMetadata();
        $reflection = $metadata->getReflectionClass();

        if (
            $reflection->implementsInterface(ContentDimensionInterface::class)
            || $reflection->implementsInterface(ContentViewInterface::class)
        ) {
            $this->addField($metadata, 'dimensionId', 'string', [
                'columnName' => 'dimensionId',
                '_custom' => [
                    'references' => [
                        'entity' => DimensionInterface::class,
                        'field' => 'id',
                        'onDelete' => 'CASCADE',
                        'onUpdate' => 'CASCADE',
                    ],
                ],
            ]);
        }

        if ($reflection->implementsInterface(SeoInterface::class)) {
            $this->addField($metadata, 'seoTitle');
            $this->addField($metadata, 'seoDescription', 'text');
            $this->addField($metadata, 'seoKeywords', 'text');
            $this->addField($metadata, 'seoCanonicalUrl', 'text');
            $this->addField($metadata, 'seoNoIndex', 'boolean');
            $this->addField($metadata, 'seoNoFollow', 'boolean');
            $this->addField($metadata, 'seoHideInSitemap', 'boolean');
        }

        if ($reflection->implementsInterface(TemplateInterface::class)) {
            $this->addField($metadata, 'templateKey', 'string', ['nullable' => false, 'length' => 32]);
            $this->addField($metadata, 'templateData', 'json', ['nullable' => false]);
        }

        if ($reflection->implementsInterface(ExcerptInterface::class)) {
            $this->addField($metadata, 'excerptTitle');
            $this->addField($metadata, 'excerptMore', 'string', ['length' => 64]);
            $this->addField($metadata, 'excerptDescription', 'text');
            $this->addField($metadata, 'excerptImage', 'integer', [
                'columnName' => 'excerptImageId',
                '_custom' => [
                    'references' => [
                        'entity' => MediaInterface::class,
                        'field' => 'id',
                        'onDelete' => 'CASCADE',
                        'onUpdate' => 'CASCADE',
                    ],
                ],
            ]);

            $this->addField($metadata, 'excerptIcon', 'integer', [
                'columnName' => 'excerptIconId',
                '_custom' => [
                    'references' => [
                        'entity' => MediaInterface::class,
                        'field' => 'id',
                        'onDelete' => 'CASCADE',
                        'onUpdate' => 'CASCADE',
                    ],
                ],
            ]);

            $this->addManyToMany($event, $metadata, 'excerptTags', TagInterface::class, 'tag_id');
            $this->addManyToMany($event, $metadata, 'excerptCategories', CategoryInterface::class, 'category_id');
        }
    }

    private function addManyToMany(
        LoadClassMetadataEventArgs $event,
        ClassMetadataInfo $metadata,
        string $name,
        string $class,
        string $inverseColumnName
    ): void {
        if ($metadata->hasAssociation($name)) {
            return;
        }

        $namingStrategy = $event->getEntityManager()->getConfiguration()->getNamingStrategy();

        $metadata->mapManyToMany([
            'fieldName' => $name,
            'targetEntity' => $class,
            'joinTable' => [
                'name' => $this->getRelationTableName($metadata, $name),
                'joinColumns' => [
                    [
                        'name' => $namingStrategy->joinKeyColumnName($metadata->getName()),
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'onUpdate' => 'CASCADE',
                    ],
                ],
                'inverseJoinColumns' => [
                    [
                        'name' => $inverseColumnName,
                        'referencedColumnName' => 'id',
                        'onDelete' => 'CASCADE',
                        'onUpdate' => 'CASCADE',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param mixed[] $mapping
     */
    private function addField(ClassMetadataInfo $metadata, string $name, string $type = 'string', array $mapping = []): void
    {
        if ($metadata->hasField($name)) {
            return;
        }

        $nullable = true;
        if ('boolean' === $type) {
            $nullable = false;
        }

        $metadata->mapField(array_merge([
            'fieldName' => $name,
            'columnName' => $name,
            'type' => $type,
            'nullable' => $nullable,
        ], $mapping));
    }

    private function getRelationTableName(ClassMetadataInfo $metadata, string $relationName): string
    {
        $inflector = new Inflector();
        $tableNameParts = explode('_', $metadata->getTableName());
        $singularName = $inflector->singularize($tableNameParts[\count($tableNameParts) - 1]) . '_';
        $tableNameParts[\count($tableNameParts) - 1] = $singularName;

        return implode('_', $tableNameParts) . $inflector->tableize($relationName);
    }
}
