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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Dimension\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;

class DimensionTest extends TestCase
{
    protected function createDimension(string $id = null, string $locale = null, bool $published = false): DimensionInterface
    {
        return new Dimension($id, $locale, $published);
    }

    public function testGetId(): void
    {
        $dimension = $this->createDimension();
        $this->assertNotNull($dimension->getId());
    }

    public function testGetIdSet(): void
    {
        $dimension = $this->createDimension('123');
        $this->assertSame('123', $dimension->getId());
    }

    public function testGetLocale(): void
    {
        $dimension = $this->createDimension();
        $this->assertNull($dimension->getLocale());
    }

    public function testGetLocaleSet(): void
    {
        $dimension = $this->createDimension(null, 'de');
        $this->assertSame('de', $dimension->getLocale());
    }

    public function testGetPublished(): void
    {
        $dimension = $this->createDimension();
        $this->assertFalse($dimension->getPublished());
    }

    public function testGetPublishedSet(): void
    {
        $dimension = $this->createDimension(null, null, true);
        $this->assertTrue($dimension->getPublished());
    }
}
