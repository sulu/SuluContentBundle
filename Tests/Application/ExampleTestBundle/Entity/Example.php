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

use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentRichEntity;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

class Example extends AbstractContentRichEntity
{
    const RESOURCE_KEY = 'examples';
    const TEMPLATE_TYPE = 'example';

    /**
     * @var mixed
     */
    public $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public static function getResourceKey(): string
    {
        return self::RESOURCE_KEY;
    }

    public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
    {
        return new ExampleDimensionContent($this, $dimension);
    }
}
