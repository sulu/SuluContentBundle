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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Mocks;

use Doctrine\Common\Collections\Collection;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionContentInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\DimensionInterface;

/**
 * Trait for composing a class that wraps a ContentRichEntityInterface mock.
 *
 * @see MockWrapper to learn why this trait is needed.
 */
trait ContentRichEntityMockWrapperTrait
{
    public static function getResourceKey(): string
    {
        return 'mock-resource-key';
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->instance->getId();
    }

    /**
     * @return Collection<int, DimensionContentInterface>
     */
    public function getDimensionContents(): Collection
    {
        return $this->instance->getDimensionContents();
    }

    public function createDimensionContent(DimensionInterface $dimension): DimensionContentInterface
    {
        return $this->instance->createDimensionContent($dimension);
    }

    public function addDimensionContent(DimensionContentInterface $dimensionContent): void
    {
        $this->instance->addDimensionContent($dimensionContent);
    }

    public function removeDimensionContent(DimensionContentInterface $dimensionContent): void
    {
        $this->instance->removeDimensionContent($dimensionContent);
    }
}
