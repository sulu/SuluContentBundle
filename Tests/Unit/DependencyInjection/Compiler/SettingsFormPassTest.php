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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\DependencyInjection\Compiler;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AuthorInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\DependencyInjection\Compiler\SettingsFormPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SettingsFormPassTest extends TestCase
{
    public function testProcess(): void
    {
        $directories = [
            __DIR__ . \DIRECTORY_SEPARATOR . 'SettingsFormXml',
        ];

        $container = $this->prophesize(ContainerBuilder::class);
        $container->getParameter('sulu_admin.forms.directories')
            ->shouldBeCalled()
            ->willReturn($directories);

        $container->setParameter('sulu_content.settings_forms', [
            'content_settings_seo' => [
                'instanceOf' => SeoInterface::class,
                'priority' => 256,
            ],
            'content_settings_author' => [
                'instanceOf' => AuthorInterface::class,
                'priority' => 128,
            ],
        ])->shouldBeCalled();

        $settingsFormPass = new SettingsFormPass();
        $settingsFormPass->process($container->reveal());
    }
}
