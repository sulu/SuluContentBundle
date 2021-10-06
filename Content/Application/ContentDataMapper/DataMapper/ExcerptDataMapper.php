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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDataMapper\DataMapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentCollectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptDataMapper implements DataMapperInterface
{
    /**
     * @var TagFactoryInterface
     */
    private $tagFactory;

    /**
     * @var CategoryFactoryInterface
     */
    private $categoryFactory;

    public function __construct(TagFactoryInterface $tagFactory, CategoryFactoryInterface $categoryFactory)
    {
        $this->tagFactory = $tagFactory;
        $this->categoryFactory = $categoryFactory;
    }

    public function map(
        array $data,
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent
    ): void {
        if (!$localizedDimensionContent instanceof ExcerptInterface) {
            return;
        }

        $this->setExcerptData($localizedDimensionContent, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setExcerptData(ExcerptInterface $dimensionContent, array $data): void
    {
        $dimensionContent->setExcerptTitle($data['excerptTitle'] ?? null);
        $dimensionContent->setExcerptDescription($data['excerptDescription'] ?? null);
        $dimensionContent->setExcerptMore($data['excerptMore'] ?? null);
        $dimensionContent->setExcerptImage($data['excerptImage'] ?? null);
        $dimensionContent->setExcerptIcon($data['excerptIcon'] ?? null);
        $dimensionContent->setExcerptTags($this->tagFactory->create($data['excerptTags'] ?? []));
        $dimensionContent->setExcerptCategories(
            $this->categoryFactory->create($data['excerptCategories'] ?? [])
        );
    }
}
