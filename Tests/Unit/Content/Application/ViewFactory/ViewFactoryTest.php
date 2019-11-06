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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewFactory;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ViewFactory\ViewFactory;
use Sulu\Bundle\ContentBundle\Content\Domain\Factory\ViewFactoryInterface;
use Sulu\Bundle\ContentBundle\TestCases\Content\ViewFactoryTestCaseTrait;

class ViewFactoryTest extends TestCase
{
    use ViewFactoryTestCaseTrait;

    protected function getViewFactoryInstance(iterable $mergers = []): ViewFactoryInterface
    {
        return new ViewFactory($mergers);
    }
}
