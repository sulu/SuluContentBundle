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

use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\PageBundle\Teaser\Teaser;
use Sulu\Component\Content\Metadata\Factory\StructureMetadataFactoryInterface;

trait ContentTeaserProviderTrait
{
    /**
     * @param mixed[] $ids
     * @param string $locale
     *
     * @return Teaser[]
     */
    public function find(array $ids, $locale): array
    {
        if (0 === \count($ids)) {
            return [];
        }

        $contentRichEntities = $this->findByIds($ids);

        return array_filter(
            array_map(
                function (ContentRichEntityInterface $contentRichEntity) use ($locale): ?Teaser {
                    $contentProjection = $this->resolveContentRichEntity($contentRichEntity, $locale);
                    $data = $this->getContentManager()->normalize($contentProjection);

                    return $this->createTeaser($contentProjection, $data, $locale);
                },
                $contentRichEntities
            )
        );
    }

    /**
     * @param mixed[] $data
     */
    protected function createTeaser(ContentProjectionInterface $contentProjection, array $data, string $locale): ?Teaser
    {
        $url = $this->getUrl($contentProjection, $data);

        if (!$url) {
            return null;
        }

        /** @var string $title */
        $title = $this->getTitle($contentProjection, $data);

        /** @var string $description */
        $description = $this->getDescription($contentProjection, $data);

        /** @var string $moreText */
        $moreText = $this->getMoreText($contentProjection, $data);

        /** @var int $mediaId */
        $mediaId = $this->getMediaId($contentProjection, $data);

        return new Teaser(
            $contentProjection->getContentId(),
            $this->getResourceKey(),
            $locale,
            $title,
            $description,
            $moreText,
            $url,
            $mediaId,
            $this->getAttributes($contentProjection, $data)
        );
    }

    protected function resolveContentRichEntity(ContentRichEntityInterface $contentRichEntity, string $locale): ?ContentProjectionInterface
    {
        $stage = $this->getShowUnpublished()
            ? DimensionInterface::STAGE_DRAFT
            : DimensionInterface::STAGE_LIVE;

        $contentProjection = $this->getContentManager()->resolve($contentRichEntity, [
            'locale' => $locale,
            'stage' => $stage,
        ]);

        $dimension = $contentProjection->getDimension();

        if ($stage !== $dimension->getStage() || $locale !== $dimension->getLocale()) {
            return null;
        }

        return $contentProjection;
    }

    /**
     * @param mixed[] $data
     */
    protected function getUrl(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        if (!$contentProjection instanceof TemplateInterface) {
            return null;
        }

        $type = $contentProjection::getTemplateType();
        $template = $contentProjection->getTemplateKey();

        $metadata = $this->getMetadataFactory()->getStructureMetadata($type, $template);

        if (!$metadata) {
            return null;
        }

        foreach ($metadata->getProperties() as $property) {
            if ('route' === $property->getType()) {
                return $contentProjection->getTemplateData()[$property->getName()] ?? null;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getTitle(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        if ($contentProjection instanceof ExcerptInterface) {
            if ($excerptTitle = $contentProjection->getExcerptTitle()) {
                return $excerptTitle;
            }
        }

        return $data['title'] ?? $data['name'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getDescription(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        if ($contentProjection instanceof ExcerptInterface) {
            if ($excerptDescription = $contentProjection->getExcerptDescription()) {
                return $excerptDescription;
            }
        }

        return $data['description'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getMoreText(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        if ($contentProjection instanceof ExcerptInterface) {
            if ($excerptMore = $contentProjection->getExcerptMore()) {
                return $excerptMore;
            }
        }

        return $data['more'] ?? null;
    }

    /**
     * @param mixed[] $data
     */
    protected function getMediaId(ContentProjectionInterface $contentProjection, array $data): ?string
    {
        if ($contentProjection instanceof ExcerptInterface) {
            if ($excerptImage = $contentProjection->getExcerptImage()) {
                return $excerptImage['id'] ?? null;
            }
        }

        return null;
    }

    /**
     * @param mixed[] $data
     *
     * @return mixed[]
     */
    protected function getAttributes(ContentProjectionInterface $contentProjection, array $data): array
    {
        return [];
    }

    protected function getShowUnpublished(): bool
    {
        return false;
    }

    /**
     * @param mixed[] $ids
     *
     * @return ContentRichEntityInterface[]
     */
    abstract protected function findByIds(array $ids): array;

    abstract protected function getResourceKey(): string;

    abstract protected function getContentManager(): ContentManagerInterface;

    abstract protected function getMetadataFactory(): StructureMetadataFactoryInterface;
}
