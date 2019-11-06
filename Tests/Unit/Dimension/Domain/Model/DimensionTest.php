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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Dimension\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\Dimension;
use Sulu\Bundle\ContentBundle\Dimension\Domain\Model\DimensionInterface;

class DimensionTest extends TestCase
{
    /**
     * @param array<string, mixed> $attributes
     */
    protected function createDimension(
        string $id = null,
        array $attributes = []
    ): DimensionInterface {
        return new Dimension($id, $attributes);
    }

    public function testGetIdSetNothing(): void
    {
        $dimension = $this->createDimension();
        $this->assertNotNull($dimension->getId());
    }

    public function testGetIdSet123(): void
    {
        $dimension = $this->createDimension('123');
        $this->assertSame('123', $dimension->getId());
    }

    public function testGetLocaleSetNothing(): void
    {
        $dimension = $this->createDimension();
        $this->assertNull($dimension->getLocale());
    }

    public function testGetLocaleSetDe(): void
    {
        $dimension = $this->createDimension(null, ['locale' => 'de']);
        $this->assertSame('de', $dimension->getLocale());
    }

    public function testGetPublishedSetNothing(): void
    {
        $dimension = $this->createDimension();
        $this->assertSame('draft', $dimension->getWorkflowStage());
    }

    public function testGetPublishedSetLive(): void
    {
        $dimension = $this->createDimension(null, ['workflowStage' => 'live']);
        $this->assertSame('live', $dimension->getWorkflowStage());
    }

    public function testGetPublishedSetDraft(): void
    {
        $dimension = $this->createDimension(null, ['workflowStage' => 'draft']);
        $this->assertSame('draft', $dimension->getWorkflowStage());
    }
}
