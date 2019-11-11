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

namespace Sulu\Bundle\ContentBundle\TestCases\Content;

use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;

/**
 * Trait to test your implementation of the TemplateInterface.
 */
trait TemplateTestCaseTrait
{
    abstract protected function getTemplateInstance(): TemplateInterface;

    public function testGetSetTemplateKey(): void
    {
        $model = $this->getTemplateInstance();
        $this->assertNull($model->getTemplateKey());
        $model->setTemplateKey('template');
        $this->assertSame('template', $model->getTemplateKey());
    }

    public function testGetSetTemplateData(): void
    {
        $model = $this->getTemplateInstance();
        $this->assertSame([], $model->getTemplateData());
        $model->setTemplateData(['data' => 'My Data']);
        $this->assertSame(['data' => 'My Data'], $model->getTemplateData());
    }

    public function testGetTypeKey(): void
    {
        $model = $this->getTemplateInstance();
        $this->assertSame('example', $model::getTemplateType());
    }
}
