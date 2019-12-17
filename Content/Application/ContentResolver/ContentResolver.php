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

use Sulu\Bundle\ContentBundle\Content\Domain\Exception\ContentNotFoundException;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ContentProjectionFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentRichEntityInterface;
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
     * @var ContentProjectionFactoryInterface
     */
    private $viewFactory;

    public function __construct(
        DimensionRepositoryInterface $dimensionRepository,
        DimensionContentRepositoryInterface $dimensionContentRepository,
        ContentProjectionFactoryInterface $viewFactory
    ) {
        $this->dimensionRepository = $dimensionRepository;
        $this->dimensionContentRepository = $dimensionContentRepository;
        $this->viewFactory = $viewFactory;
    }

    public function resolve(ContentRichEntityInterface $contentRichEntity, array $dimensionAttributes): ContentProjectionInterface
    {
        $dimensionCollection = $this->dimensionRepository->findByAttributes($dimensionAttributes);

        if (0 === \count($dimensionCollection)) {
            throw new ContentNotFoundException($contentRichEntity, $dimensionAttributes);
        }

        $dimensionContentCollection = $this->dimensionContentRepository->load($contentRichEntity, $dimensionCollection);

        if (0 === \count($dimensionContentCollection)) {
            throw new ContentNotFoundException($contentRichEntity, $dimensionAttributes);
        }

        return $this->viewFactory->create($dimensionContentCollection);
    }
}
