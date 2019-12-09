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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

interface ContentFacadeInterface
{
    /**
     * @param mixed[] $dimensionAttributes
     */
    public function load(ContentInterface $content, array $dimensionAttributes): ContentViewInterface;

    /**
     * @param mixed[] $data
     * @param mixed[] $dimensionAttributes
     */
    public function persist(ContentInterface $content, array $data, array $dimensionAttributes): ContentViewInterface;

    /**
     * @return mixed[]
     */
    public function resolve(ContentViewInterface $contentView): array;

    /**
     * @param mixed[] $sourceDimensionAttributes
     * @param mixed[] $targetDimensionAttributes
     */
    public function copy(
        ContentInterface $sourceContent,
        array $sourceDimensionAttributes,
        ContentInterface $targetContent,
        array $targetDimensionAttributes
    ): ContentViewInterface;

    /**
     * @param mixed[] $dimensionAttributes
     */
    public function transition(
        ContentInterface $content,
        array $dimensionAttributes,
        string $transitionName
    ): ContentViewInterface;
}
