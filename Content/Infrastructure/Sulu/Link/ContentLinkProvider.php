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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Link;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Traits\FindContentRichEntitiesTrait;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Traits\ResolveContentDimensionUrlTrait;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Traits\ResolveContentTrait;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkItem;
use Sulu\Bundle\MarkupBundle\Markup\Link\LinkProviderInterface;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

abstract class ContentLinkProvider implements LinkProviderInterface
{
    use FindContentRichEntitiesTrait;
    use ResolveContentDimensionUrlTrait;
    use ResolveContentTrait;

    /**
     * @var StructureMetadataFactoryInterface
     */
    protected $structureMetadataFactory;

    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $contentRichEntityClass;

    public function __construct(
        ContentManagerInterface $contentManager,
        StructureMetadataFactoryInterface $structureMetadataFactory,
        EntityManagerInterface $entityManager,
        string $contentRichEntityClass
    ) {
        $this->contentManager = $contentManager;
        $this->structureMetadataFactory = $structureMetadataFactory;
        $this->entityManager = $entityManager;
        $this->contentRichEntityClass = $contentRichEntityClass;
    }

    public function preload(array $hrefs, $locale, $published = true): array
    {
        if (0 === \count($hrefs)) {
            return [];
        }
        $items = $this->findEntitiesByIds($hrefs);

        return
            \array_values(
                \array_filter(
                    \array_map(function(ContentRichEntityInterface $contentRichEntity) use ($locale, $published) {
                        /** @var DimensionContentInterface|null $resolvedDimensionContent */
                        $resolvedDimensionContent = $this->resolveContent($contentRichEntity, $locale, !$published);

                        if (!$resolvedDimensionContent) {
                            return null;
                        }

                        $data = $this->contentManager->normalize($resolvedDimensionContent);

                        return new LinkItem(
                            $contentRichEntity->getId(),
                            (string) $this->getTitle($resolvedDimensionContent, $data),
                            (string) $this->getUrl($resolvedDimensionContent, $data),
                            $published
                        );
                    }, $items)
                )
            );
    }

    /**
     * @param mixed[] $data
     */
    protected function getTitle(DimensionContentInterface $dimensionContent, array $data): ?string
    {
        return $data['title'] ?? $data['name'] ?? null;
    }

    protected function getEntityIdField(): string
    {
        return 'id';
    }

    protected function getContentRichEntityClass(): string
    {
        return $this->contentRichEntityClass;
    }

    protected function getStructureMetadataFactory(): StructureMetadataFactoryInterface
    {
        return $this->structureMetadataFactory;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    protected function getContentManager(): ContentManagerInterface
    {
        return $this->contentManager;
    }
}
