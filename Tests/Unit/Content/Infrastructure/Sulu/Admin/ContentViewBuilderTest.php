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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Infrastructure\Sulu\Admin;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\AdminBundle\Admin\View\FormViewBuilderInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactory;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilder;
use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Admin\ContentViewBuilderInterface;

class ContentViewBuilderTest extends TestCase
{
    protected function getContentViewBuilder(): ContentViewBuilderInterface
    {
        return new ContentViewBuilder(new ViewBuilderFactory());
    }

    public function testBuild(): void
    {
        $contentViewBuilder = $this->getContentViewBuilder();

        $viewCollection = new ViewCollection();
        $contentViewBuilder->build(
            $viewCollection,
            'examples',
            'example',
            'edit_parent_key',
            'add_parent_key'
        );

        /** @var FormViewBuilderInterface $editContentProjection */
        $editContentProjection = $viewCollection->get('edit_parent_key.content');
        $editExcerptView = $viewCollection->get('edit_parent_key.excerpt');
        $editSeoView = $viewCollection->get('edit_parent_key.seo');
        $addContentProjection = $viewCollection->get('add_parent_key.content');

        $this->assertCount(4, $viewCollection->all());

        // Test Edit Content View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editContentProjection);
        $this->assertSame('example', $editContentProjection->getView()->getOption('formKey'));

        // Test Edit Excerpt View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editExcerptView);
        $this->assertSame('content_excerpt', $editExcerptView->getView()->getOption('formKey'));

        // Test Edit Seo View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $editSeoView);
        $this->assertSame('content_seo', $editSeoView->getView()->getOption('formKey'));

        // Test Add Content View
        $this->assertInstanceOf(FormViewBuilderInterface::class, $addContentProjection);
        $this->assertSame('example', $addContentProjection->getView()->getOption('formKey'));
    }
}
