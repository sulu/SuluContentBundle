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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentResolver;

use Sulu\Bundle\ContentBundle\Content\Application\ContentMerger\ContentMergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionContentRepositoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Repository\DimensionRepositoryInterface;

class ContentResolver implements ContentResolverInterface
{
    /**
     * @var DimensionRepositoryInterface
     */
    private $dimensionRepository;

    /**
     * @var DimensionContentRepositoryInterface
     */
    private $dimensionContentRepository;

    /**
     * @var ContentMergerInterface
     */
    private $contentMerger;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ContentMergerInterface $contentMerger
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->contentMerger = $contentMerger;
    }

    public function resolve(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): DimensionContentInterface
    {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);

        if (0 === \count($dimensionCollection)) {
            throw new ContentNotFoundException($contentRichEntity, $dimensionAttributes);
        }

        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        if (0 === \count($dimensionContentCollection)) {
            throw new ContentNotFoundException($contentRichEntity, $dimensionAttributes);
        }

        return $this->contentMerger->merge($dimensionContentCollection);
    }
}
