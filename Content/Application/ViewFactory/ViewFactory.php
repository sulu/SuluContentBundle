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

namespace Sulu\Bundle\ContentBundle\Content\Application\ViewFactory;

use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\Merger\MergerInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentViewInterface;

class ViewFactory implements ViewFactoryInterface
{
    /**
     * @var iterable<MergerInterface>
     */
    private $mergers = [];

    /**
     * @param iterable<MergerInterface> $mergers
     */
    public function __construct(iterable $mergers)
    {
        $this->mergers = $mergers;
    }

    public function create(array $contentDimensions): ContentViewInterface
    {
        if (empty($contentDimensions)) {
            throw new \RuntimeException('Expected at least one contentDimension given.');
        }

        $contentView = $contentDimensions[\count($contentDimensions) - 1]->createViewInstance();

        foreach ($contentDimensions as $contentDimension) {
            /** @var MergerInterface $merger */
            foreach ($this->mergers as $merger) {
                $merger->merge($contentView, $contentDimension);
            }
        }

        return $contentView;
    }
}
