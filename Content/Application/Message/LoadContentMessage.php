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

class LoadContentMessage
{
    /**
     * @var ContentInterface
     */
    private $content;

    /**
     * @var array<string, string|int|float|bool|null>
     */
    private $dimensionAttributes;

    /**
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    public function __construct(ContentInterface $content, array $dimensionAttributes)
    {
        $this->content = $content;
        $this->dimensionAttributes = $dimensionAttributes;
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    /**
     * @return array<string, string|int|float|bool|null>
     */
    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }
}
