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

namespace Sulu\Bundle\ContentBundle\Content\Application\ContentDimensionFactory\Mapper;

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
        object $contentDimension,
        ?object $localizedContentDimension = null
    ): void {
        if (!$contentDimension instanceof ExcerptInterface) {
            return;
        }

        if ($localizedContentDimension) {
            if (!$localizedContentDimension instanceof ExcerptInterface) {
                throw new \RuntimeException(sprintf(
                    'Expected "$localizedContentDimension" from type "%s" but "%s" given.',
                    ExcerptInterface::class,
                    \get_class($localizedContentDimension)
                ));
            }

            $this->setExcerptData($localizedContentDimension, $data);

            return;
        }

        $this->setExcerptData($contentDimension, $data);
    }

    /**
     * @param mixed[] $data
     */
    private function setExcerptData(ExcerptInterface $contentDimension, array $data): void
    {
        $contentDimension->setExcerptTitle($data['excerptTitle'] ?? null);
        $contentDimension->setExcerptDescription($data['excerptDescription'] ?? null);
        $contentDimension->setExcerptMore($data['excerptMore'] ?? null);
        $contentDimension->setExcerptImage($data['excerptImage'] ?? null);
        $contentDimension->setExcerptIcon($data['excerptIcon'] ?? null);
        $contentDimension->setExcerptTags($this->tagFactory->create($data['excerptTags'] ?? []));
        $contentDimension->setExcerptCategories(
            $this->categoryFactory->create($data['excerptCategories'] ?? [])
        );
    }
}
