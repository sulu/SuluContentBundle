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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentCopier;

use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentCopier implements ContentCopierInterface
{
    /**
     * @var ContentLoaderInterface
     */
    private $contentLoader;

    /**
     * @var ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var ContentPersisterInterface
     */
    private $contentPersister;

    /**
     * @var ApiViewResolverInterface
     */
    private $contentResolver;

    public function __construct(
        ContentLoaderInterface $contentLoader,
        ViewFactoryInterface $viewFactory,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver
    ) {
        $this->contentLoader = $contentLoader;
        $this->viewFactory = $viewFactory;
        $this->contentPersister = $contentPersister;
        $this->contentResolver = $contentResolver;
    }

    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentViewInterface {
        $sourceContentView = $this->contentLoader->load($sourceContentRichEntity, $sourceDimensionAttributes);

        return $this->copyFromContentView($sourceContentView, $targetContentRichEntity, $targetDimensionAttributes);
    }

    public function copyFromContentDimensionCollection(
        ContentDimensionCollectionInterface $contentDimensionCollection,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentViewInterface {
        $sourceContentView = $this->viewFactory->create($contentDimensionCollection);

        return $this->copyFromContentView($sourceContentView, $targetContentRichEntity, $targetDimensionAttributes);
    }

    public function copyFromContentView(
        ContentViewInterface $sourceContentView,
        ContentRichEntityInterface $targetContentRichENtity,
        array $targetDimensionAttributes
    ): ContentViewInterface {
        $data = $this->contentResolver->resolve($sourceContentView);

        return $this->contentPersister->persist($targetContentRichENtity, $data, $targetDimensionAttributes);
    }
}
