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
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WebspaceInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WebspaceTrait;

class WebspaceTraitTest extends TestCase
{
    protected function getWebspaceInstance(): WebspaceInterface
    {
        return new class() implements WebspaceInterface {
            use WebspaceTrait;
        };
    }

    public function testGetSetMainWebspace(): void
    {
        $model = $this->getWebspaceInstance();
        $this->assertNull($model->getMainWebspace());
        $model->setMainWebspace('example');
        $this->assertSame('example', $model->getMainWebspace());
    }

    public function testGetSetAdditionalWebspaces(): void
    {
        $model = $this->getWebspaceInstance();
        $this->assertSame([], $model->getAdditionalWebspaces());
        $model->setAdditionalWebspaces(['example', 'example2']);
        $this->assertSame(['example', 'example2'], $model->getAdditionalWebspaces());
    }

    public function testMainWebspaceAlwaysAdditionalWebspaces(): void
    {
        $model = $this->getWebspaceInstance();
        $model->setMainWebspace('example');

        $this->assertSame('example', $model->getMainWebspace());
        $this->assertSame(['example'], $model->getAdditionalWebspaces());
    }

    public function testAdditionalWebspacesWithoutMainWebspace(): void
    {
        $model = $this->getWebspaceInstance();
        $model->setMainWebspace('example');
        $model->setAdditionalWebspaces(['example-2']);

        $this->assertSame('example', $model->getMainWebspace());
        $this->assertSame(['example-2', 'example'], $model->getAdditionalWebspaces());
    }
}
