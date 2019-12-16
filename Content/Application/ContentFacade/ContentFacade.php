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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentFacade;

use Sulu\Bundle\ContentBundle\Content\Application\ContentCopier\ContentCopierInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentLoader\ContentLoaderInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentPersister\ContentPersisterInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentWorkflow\ContentWorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;

class ContentFacade implements ContentFacadeInterface
{
    /**
     * @var ContentLoaderInterface
     */
    private $contentLoader;

    /**
     * @var ContentPersisterInterface
     */
    private $contentPersister;

    /**
     * @var ApiViewResolverInterface
     */
    private $contentResolver;

    /**
     * @var ContentCopierInterface
     */
    private $contentCopier;

    /**
     * @var ContentWorkflowInterface
     */
    private $contentWorkflow;

    public function __construct(
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver,
        ContentCopierInterface $contentCopier,
        ContentWorkflowInterface $contentWorkflow
    ) {
        $this->contentLoader = $contentLoader;
        $this->contentPersister = $contentPersister;
        $this->contentResolver = $contentResolver;
        $this->contentCopier = $contentCopier;
        $this->contentWorkflow = $contentWorkflow;
    }

    public function load(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): ContentProjectionInterface
    {
        return $this->contentLoader->load($contentRichEntity, $dimensionAttributes);
    }

    public function persist(ContentRichEntityInterface $contentRichEntity, array $data, array $dimensionAttributes): ContentProjectionInterface
    {
        return $this->contentPersister->persist($contentRichEntity, $data, $dimensionAttributes);
    }

    public function resolve(ContentProjectionInterface $contentProjection): array
    {
        return $this->contentResolver->resolve($contentProjection);
    }

    public function copy(
        ContentRichEntityInterface $sourceContentRichEntity,
        array $sourceDimensionAttributes,
        ContentRichEntityInterface $targetContentRichEntity,
        array $targetDimensionAttributes
    ): ContentProjectionInterface {
        return $this->contentCopier->copy(
            $sourceContentRichEntity,
            $sourceDimensionAttributes,
            $targetContentRichEntity,
            $targetDimensionAttributes
        );
    }

    public function applyTransition(
        ContentRichEntityInterface $contentRichEntity,
        array $dimensionAttributes,
        string $transitionName
    ): ContentProjectionInterface {
        return $this->contentWorkflow->apply($contentRichEntity, $dimensionAttributes, $transitionName);
    }
}
