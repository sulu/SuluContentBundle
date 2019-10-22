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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Dimension;

class DimensionTest extends TestCase
{
    /**
     * @var Dimension
     */
    private $dimension;

    public function setUp(): void
    {
        $this->dimension = new Dimension();
    }

    public function testGetId(): void
    {
        $this->assertNotNull($this->dimension->getId());
    }
}
