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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\TestCases\Content\TemplateTestCaseTrait;

class TemplateTraitTest extends TestCase
{
    use TemplateTestCaseTrait;

    protected function getTemplateInstance(): TemplateInterface
    {
        return new class() implements TemplateInterface {
            use TemplateTrait;
        };
    }
}
