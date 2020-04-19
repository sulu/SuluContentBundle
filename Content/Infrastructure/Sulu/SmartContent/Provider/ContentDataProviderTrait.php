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
use Sulu\Bundle\WebsiteBundle\ReferenceStore\ReferenceStoreInterface;
use Sulu\Component\Content\Compat\PropertyParameter;
use Sulu\Component\SmartContent\ArrayAccessItem;
use Sulu\Component\SmartContent\ItemInterface;
use Symfony\Component\HttpFoundation\RequestStack;

trait ContentDataProviderTrait
{
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
     * Returns additional options for query creation.
     *
     * @param PropertyParameter[] $propertyParameter
     * @param mixed[] $options
     *
     * @return mixed[]
     */
    protected function getOptions(
        array $propertyParameter,
        array $options = []
    ) {
        $isAdmin = 'admin' === $this->getSuluContext();
        $isPreview = false;

        $request = $this->getRequestStack()->getMasterRequest();

        if ($request) {
            $isPreview = true === $request->attributes->getBoolean('preview');
        }

        return [
            'showUnpublished' => $isAdmin || $isPreview,
        ];
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

    abstract protected function getContentManager(): ContentManagerInterface;

    abstract protected function getRequestStack(): RequestStack;

    abstract protected function getSuluContext(): string;

    abstract protected function getReferenceStore(): ?ReferenceStoreInterface;

    /**
     * @param mixed[] $data
     */
    abstract protected function createDataItem(ContentProjectionInterface $contentProjection, array $data): ItemInterface;
}
