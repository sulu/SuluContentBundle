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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ViewResolver;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\ExcerptResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\TemplateResolver;
use Sulu\Bundle\ContentBundle\TestCases\Content\ApiViewResolverTestCaseTrait;

class ApiViewResolverTest extends TestCase
{
    use ApiViewResolverTestCaseTrait;

    protected function createApiViewResolverInstance(): ApiViewResolverInterface
    {
        return new ApiViewResolver([
            new ExcerptResolver(),
            new TemplateResolver(),
        ]);
    }
}
