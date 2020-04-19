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

namespace Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\Provider;

use Sulu\Bundle\ContentBundle\Content\Application\ContentManager\ContentManagerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\SmartContent\DataItem\ContentDataItem;
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Serializer\ArraySerializerInterface;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\Configuration\BuilderInterface;
use Sulu\Component\SmartContent\ItemInterface;
use Sulu\Component\SmartContent\Orm\BaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;

class ContentDataProvider extends BaseDataProvider
{
    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var ReferenceStoreInterface|null
     */
    protected $referenceStore;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $arraySerializer,
        ContentManagerInterface $contentManager,
        ReferenceStoreInterface $referenceStore = null
    ) {
        parent::__construct($repository, $arraySerializer);

        $this->contentManager = $contentManager;
        $this->referenceStore = $referenceStore;

        $configurationBuilder = static::createConfigurationBuilder();

        $this->configure($configurationBuilder);

        $this->configuration = $configurationBuilder->getConfiguration();
    }

    protected function configure(BuilderInterface $builder): void
    {
        $builder
            ->enableTags()
            ->enableCategories()
            ->enableLimit()
            ->enablePagination()
            ->enablePresentAs()
            ->enableSorting(
                [
                    ['column' => 'workflowPublished', 'title' => 'sulu_content.published'],
                ]
            );
    }

    /**
     * @param ContentProjectionInterface[] $data
     *
     * @return mixed[]
     */
    protected function decorateDataItems(array $data): array
    {
        return array_map(
            function (ContentProjectionInterface $contentProjection) {
                $contentProjectionData = $this->getContentProjectionData($contentProjection);

                return $this->createDataItem($contentProjection, $contentProjectionData);
            },
            $data
        );
    }

    /**
     * Decorates result as resource item.
     *
     * @param ContentProjectionInterface[] $data
     * @param string $locale
     *
     * @return ArrayAccessItem[]
     */
    protected function decorateResourceItems(array $data, $locale): array
    {
        return array_map(
            function (ContentProjectionInterface $contentProjection) {
                $contentProjectionData = $this->getContentProjectionData($contentProjection);
                $id = $this->getIdForItem($contentProjection);

                if (null !== $this->getReferenceStore()) {
                    $this->getReferenceStore()->add($id);
                }

                return new ArrayAccessItem($id, $contentProjectionData, $contentProjection);
            },
            $data
        );
    }

    /**
     * @param ContentProjectionInterface $contentProjection
     *
     * @return mixed
     */
    protected function getIdForItem($contentProjection)
    {
        return $contentProjection->getContentId() ?: null;
    }

    /**
     * @return mixed[]
     */
    protected function getContentProjectionData(ContentProjectionInterface $contentProjection): array
    {
        return $this->getContentManager()->normalize($contentProjection);
    }

    /**
     * @param mixed[] $data
     */
    protected function createDataItem(ContentProjectionInterface $contentProjection, array $data): ItemInterface
    {
        return new ContentDataItem($contentProjection, $data);
    }

    protected function getContentManager(): ContentManagerInterface
    {
        return $this->contentManager;
    }

    protected function getReferenceStore(): ?ReferenceStoreInterface
    {
        return $this->referenceStore;
    }
}
