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
use Sulu\Component\SmartContent\Configuration\BuilderInterface;
use Sulu\Component\SmartContent\ItemInterface;
use Sulu\Component\SmartContent\Orm\BaseDataProvider as SuluBaseDataProvider;
use Sulu\Component\SmartContent\Orm\DataProviderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContentDataProvider extends SuluBaseDataProvider
{
    use ContentDataProviderTrait;

    /**
     * @var ContentManagerInterface
     */
    protected $contentManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var string
     */
    protected $suluContext;

    /**
     * @var ReferenceStoreInterface|null
     */
    protected $referenceStore;

    public function __construct(
        DataProviderRepositoryInterface $repository,
        ArraySerializerInterface $arraySerializer,
        ContentManagerInterface $contentManager,
        RequestStack $requestStack,
        string $suluContext,
        ReferenceStoreInterface $referenceStore = null
    ) {
        parent::__construct($repository, $arraySerializer);

        $this->contentManager = $contentManager;
        $this->requestStack = $requestStack;
        $this->suluContext = $suluContext;
        $this->referenceStore = $referenceStore;

        $configurationBuilder = static::createConfigurationBuilder();

        $this->configure($configurationBuilder);

        $this->configuration = $configurationBuilder->getConfiguration();
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

    protected function getRequestStack(): RequestStack
    {
        return $this->requestStack;
    }

    protected function getSuluContext(): string
    {
        return $this->suluContext;
    }

    protected function getReferenceStore(): ?ReferenceStoreInterface
    {
        return $this->referenceStore;
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
}
