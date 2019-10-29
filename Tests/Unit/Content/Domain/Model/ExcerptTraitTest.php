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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Domain\Model;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\TestCases\Content\ExcerptTestCaseTrait;

class ExcerptTraitTest extends TestCase
{
    use ExcerptTestCaseTrait;

    protected function getExcerptInstance(): ExcerptInterface
    {
        return new class() implements ExcerptInterface {
            use ExcerptTrait;
        };
    }
}
