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

namespace Sulu\Bundle\ContentBundle\Content\Application\Message;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentInterface;

class CopyContentDimensionMessage
{
    /**
     * @var ContentInterface
     */
    private $content;

    /**
     * @var mixed[]
     */
    private $fromDimensionAttributes;

    /**
     * @var string
     */
    private $toDimensionAttributes;

    /**
     * @param mixed[] $fromDimensionAttributes
     * @param mixed[] $toDimensionAttributes
     */
    public function __construct(ContentInterface $content, array $fromDimensionAttributes, array $toDimensionAttributes)
    {
        $this->content = $content;
        $this->fromDimensionAttributes = $fromDimensionAttributes;
        $this->toDimensionAttributes = $toDimensionAttributes;
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function getFromDimensionAttributes(): array
    {
        return $this->fromDimensionAttributes;
    }

    /**
     * @return mixed[]
     */
    public function getToDimensionAttributes(): array
    {
        return $this->toDimensionAttributes;
    }
}
