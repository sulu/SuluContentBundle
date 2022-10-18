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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Teaser;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentMetadataInspector\ContentMetadataInspectorInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\PageBundle\Teaser\Provider\TeaserProviderInterface;
use Sulu\Bundle\PageBundle\Teaser\Teaser;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

abstract class ContentTeaserProvider implements TeaserProviderInterface
{
    public const CONTENT_RICH_ENTITY_ALIAS = ContentWorkflowInterface::CONTENT_RICH_ENTITY_CONTEXT_KEY;

    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var ContentMetadataInspectorInterface
     */
    private $contentMetadataInspector;

    /**
     * @var StructureMetadataFactoryInterface
     */
    protected $metadataFactory;

    /**
     * @var class-string<ContentRichEntityInterface>
     */
    protected $contentRichEntityClass;

    /**
     * @var bool
     */
    protected $showDrafts;

    /**
     * @param class-string<ContentRichEntityInterface> $contentRichEntityClass
     * @param bool $showDrafts Inject parameter "sulu_document_manager.show_drafts" here
     */
    public function __construct(
        ContentManagerInterface $contentManager,
        EntityManagerInterface $entityManager,
        ContentMetadataInspectorInterface $contentMetadataInspector,
        StructureMetadataFactoryInterface $metadataFactory,
        string $contentRichEntityClass,
        bool $showDrafts
    ) {
        $this->contentManager = $contentManager;
        $this->entityManager = $entityManager;
        $this->contentMetadataInspector = $contentMetadataInspector;
        $this->metadataFactory = $metadataFactory;
        $this->contentRichEntityClass = $contentRichEntityClass;
        $this->showDrafts = $showDrafts;
    }

    /**
     * @param array<int, string|int> $ids
     * @param string $locale
     *
     * @return Teaser[]
     */
    public function find(array $ids, $locale): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $contentRichEntities = $this->findEntitiesByIds($ids);

        return \array_values(
            \array_filter(
                \array_map(
                    function(ContentRichEntityInterface $contentRichEntity) use ($locale): ?Teaser {
                        $resolvedDimensionContent = $this->resolveContent($contentRichEntity, $locale);

                        if (!$resolvedDimensionContent) {
                            return null;
                        }

                        $data = $this->contentManager->normalize($resolvedDimensionContent);

                        return $this->createTeaser($resolvedDimensionContent, $data, $locale);
                    },
                    $contentRichEntities
                )
            )
        );
    }

    /**
     * @param mixed[] $data
     */
    protected function createTeaser(DimensionContentInterface $dimensionContent, array $data, string $locale): ?Teaser
    {
        $url = $this->getUrl($dimensionContent, $data);

        if (!$url) {
            return null;
        }

        /** @var string|int $id */
        $id = $dimensionContent->getResource()->getId();

        /** @var string $title */
        $title = $this->getTitle($dimensionContent, $data);

        /** @var string $description */
        $description = $this->getDescription($dimensionContent, $data);

        /** @var string $moreText */
        $moreText = $this->getMoreText($dimensionContent, $data);

        /** @var int $mediaId */
        $mediaId = $this->getMediaId($dimensionContent, $data);

        return new Teaser(
            $id,
            $this->getResourceKey(),
            $locale,
            $title,
            $description,
            $moreText,
            $url,
            $mediaId,
            $this->getAttributes($dimensionContent, $data)
        );
    }

    protected function resolveContent(ContentRichEntityInterface $contentRichEntity, string $locale): ?DimensionContentInterface
    {
        $stage = $this->showDrafts
            // TODO FIXME add testcase for it
            ? DimensionContentInterface::STAGE_DRAFT // @codeCoverageIgnore
            : DimensionContentInterface::STAGE_LIVE;

        try {
            $resolvedDimensionContent = $this->contentManager->resolve($contentRichEntity, [
                'locale' => $locale,
                'stage' => $stage,
            ]);
        } catch (ContentNotFoundException $exception) {
            return null;
        }

        if ($stage !== $resolvedDimensionContent->getStage() || $locale !== $resolvedDimensionContent->getLocale()) {
            return null;
        }

        return $resolvedDimensionContent;
    }

    /**
     * @param mixed[] $data
     */
    protected function getUrl(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        if (!$dimensionContent instanceof TemplateInterface) {
            // TODO FIXME add testcase for it
            return null; // @codeCoverageIgnore
        }

        $type = $dimensionContent::getTemplateType();
        $template = $dimensionContent->getTemplateKey();

        $metadata = $this->metadataFactory->getStructureMetadata($type, $template);

        if (!$metadata) {
            // TODO FIXME add testcase for it
            return null; // @codeCoverageIgnore
        }

        foreach ($metadata->getProperties() as $property) {
            if ('route' === $property->getType()) {
                /** @var string|null */
                return $dimensionContent->getTemplateData()[$property->getName()] ?? null;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getTitle(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        if ($dimensionContent instanceof ExcerptInterface) {
            if ($excerptTitle = $dimensionContent->getExcerptTitle()) {
                return $excerptTitle;
            }
        }

        return $data['title'] ?? $data['name'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getDescription(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        if ($dimensionContent instanceof ExcerptInterface) {
            if ($excerptDescription = $dimensionContent->getExcerptDescription()) {
                return $excerptDescription;
            }
        }

        /** @var string|null */
        return $data['description'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getMoreText(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        if ($dimensionContent instanceof ExcerptInterface) {
            if ($excerptMore = $dimensionContent->getExcerptMore()) {
                return $excerptMore;
            }
        }

        /** @var string|null */
        return $data['more'] ?? $data['moreText'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getMediaId(DimensionContentInterface $dimensionContent, array $data): ?int
    {
        if ($dimensionContent instanceof ExcerptInterface) {
            if ($excerptImage = $dimensionContent->getExcerptImage()) {
                // TODO FIXME create unit test for this
                /** @var int|null */
                return $excerptImage['id'] ?? null; // @codeCoverageIgnore
            }
        }

        return null;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function getAttributes(DimensionContentInterface $dimensionContent, array $data): array
    {
        return [];
    }

    /**
     * @param array<int, string|int> $ids
     *
     * @return ContentRichEntityInterface[]
     */
    protected function findEntitiesByIds(array $ids): array
    {
        $entityIdField = $this->getEntityIdField();
        $classMetadata = $this->entityManager->getClassMetadata($this->contentRichEntityClass);

        /** @var ContentRichEntityInterface[] $entities */
        $entities = $this->entityManager->createQueryBuilder()
            ->select(self::CONTENT_RICH_ENTITY_ALIAS)
            ->from($this->contentRichEntityClass, self::CONTENT_RICH_ENTITY_ALIAS)
            ->where(self::CONTENT_RICH_ENTITY_ALIAS . '.' . $entityIdField . ' IN (:ids)')
            ->getQuery()
            ->setParameter('ids', $ids)
            ->getResult();

        $idPositions = \array_flip($ids);

        \usort(
            $entities,
            function(ContentRichEntityInterface $a, ContentRichEntityInterface $b) use ($idPositions, $classMetadata, $entityIdField) {
                $aId = $classMetadata->getIdentifierValues($a)[$entityIdField];
                $bId = $classMetadata->getIdentifierValues($b)[$entityIdField];

                return $idPositions[$aId] - $idPositions[$bId];
            }
        );

        return $entities;
    }

    protected function getEntityIdField(): string
    {
        return 'id';
    }

    protected function getResourceKey(): string
    {
        $dimensionContentClass = $this->contentMetadataInspector->getDimensionContentClass($this->contentRichEntityClass);

        return $dimensionContentClass::getResourceKey();
    }
}
