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

namespace Sulu\Bundle\ContentBundle\Content\Application\DimensionContentFactory\Mapper;

use Sulu\Bundle\ContentBundle\Content\Domain\Factory\CategoryFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\TagFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;

class ExcerptMapper implements MapperInterface
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
        object $dimensionContent,
        ?object $localizedDimensionContent = null
    ): void {
        if (!$dimensionContent instanceof ExcerptInterface) {
            return;
        }

        if ($localizedDimensionContent) {
            if (!$localizedDimensionContent instanceof ExcerptInterface) {
                throw new \RuntimeException(sprintf('Expected "$localizedDimensionContent" from type "%s" but "%s" given.', ExcerptInterface::class, \get_class($localizedDimensionContent)));
            }

            $this->setExcerptData($localizedDimensionContent, $data);

            return;
        }

        $this->setExcerptData($dimensionContent, $data);
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
