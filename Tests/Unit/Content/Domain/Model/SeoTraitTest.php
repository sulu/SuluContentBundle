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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\TestCases\Content\SeoTestCaseTrait;

class SeoTraitTest extends TestCase
{
    use SeoTestCaseTrait;

    protected function getSeoInstance(): SeoInterface
    {
        return new class() implements SeoInterface {
            use SeoTrait;
        };
    }
}
