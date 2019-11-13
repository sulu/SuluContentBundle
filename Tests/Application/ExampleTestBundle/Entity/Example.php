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

namespace Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContent;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentDimensionInterface;

class Example extends AbstractContent
{
    const RESOURCE_KEY = 'examples';
    const TYPE_KEY = 'example';

    /**
     * @var int
     */
    public $id;

    public function getId(): int
    {
        return $this->id;
    }

    public static function getResourceKey(): string
    {
        return self::RESOURCE_KEY;
    }

    public function createDimension(string $dimensionId): ContentDimensionInterface
    {
        return new ExampleDimension($this, $dimensionId);
    }
}
