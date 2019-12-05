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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ContentCopier implements ContentCopierInterface
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

    public function __construct(
        ContentLoaderInterface $contentLoader,
        ContentPersisterInterface $contentPersister,
        ApiViewResolverInterface $contentResolver
    ) {
        $this->contentLoader = $contentLoader;
        $this->contentPersister = $contentPersister;
        $this->contentResolver = $contentResolver;
    }

    public function copy(
        ContentInterface $sourceContent,
        array $sourceDimensionAttributes,
        ContentInterface $targetContent,
        array $targetDimensionAttributes
    ): ContentViewInterface {
        $sourceContentView = $this->contentLoader->load($sourceContent, $sourceDimensionAttributes);
        $data = $this->contentResolver->resolve($sourceContentView);

        return $this->contentPersister->persist($targetContent, $data, $targetDimensionAttributes);
    }
}
