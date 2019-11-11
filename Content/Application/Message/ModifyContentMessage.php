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

class ModifyContentMessage
{
    /**
     * @var ContentInterface
     */
    private $content;

    /**
     * @var mixed[]
     */
    private $data;

    /**
     * @var array<string, string|int|float|bool|null>
     */
    private $dimensionAttributes;

    /**
     * @param mixed[] $data
     * @param array<string, string|int|float|bool|null> $dimensionAttributes
     */
    public function __construct(ContentInterface $content, array $data, array $dimensionAttributes)
    {
        $this->content = $content;
        $this->data = $data;
        $this->dimensionAttributes = $dimensionAttributes;
    }

    public function getContent(): ContentInterface
    {
        return $this->content;
    }

    /**
     * @return mixed[]
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @return array<string, string|int|float|bool|null>
     */
    public function getDimensionAttributes(): array
    {
        return $this->dimensionAttributes;
    }
}
