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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

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

    public function load(ContentInterface $content, array $dimensionAttributes): ContentViewInterface
    {
        return $this->contentLoader->load($content, $dimensionAttributes);
    }

    public function persist(ContentInterface $content, array $data, array $dimensionAttributes): ContentViewInterface
    {
        return $this->contentPersister->persist($content, $data, $dimensionAttributes);
    }

    public function resolve(ContentViewInterface $contentView): array
    {
        return $this->contentResolver->resolve($contentView);
    }

    public function copy(
        ContentInterface $sourceContent,
        array $sourceDimensionAttributes,
        ContentInterface $targetContent,
        array $targetDimensionAttributes
    ): ContentViewInterface {
        return $this->contentCopier->copy(
            $sourceContent,
            $sourceDimensionAttributes,
            $targetContent,
            $targetDimensionAttributes
        );
    }

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function transition(
        ContentInterface $content,
        array $dimensionAttributes,
        string $transitionName
    ): ContentViewInterface {
        return $this->contentWorkflow->transition($content, $dimensionAttributes, $transitionName);
    }
}
