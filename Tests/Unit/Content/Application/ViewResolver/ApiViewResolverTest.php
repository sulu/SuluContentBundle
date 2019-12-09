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
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\ExcerptResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\TemplateResolver;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\Resolver\WorkflowResolver;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ApiViewResolverTest extends TestCase
{
    protected function createApiViewResolverInstance(): ApiViewResolverInterface
    {
        return new ApiViewResolver([
            new ExcerptResolver(),
            new TemplateResolver(),
            new WorkflowResolver(),
        ]);
    }

    public function testResolveSimple(): void
    {
        $contentView = new class() extends AbstractContentView {
            protected $id = 2;
            protected $dimensionId = '123-456';

            public function getContentId()
            {
                return 5;
            }
        };

        $apiViewResolver = $this->createApiViewResolverInstance();
        $this->assertSame([
            'dimensionId' => '123-456',
            'id' => 5,
        ], $apiViewResolver->resolve($contentView));
    }

    public function testResolveFull(): void
    {
        $contentView = new class() extends AbstractContentView implements ExcerptInterface, SeoInterface, TemplateInterface, WorkflowInterface {
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;
            use WorkflowTrait;

            protected $id = 2;
            protected $dimensionId = '123-456';

            public function getContentId()
            {
                return 5;
            }

            public function getTemplateType(): string
            {
                return 'example';
            }
        };

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->willReturn(1);
        $tag1->getName()->willReturn('Tag 1');
        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getId()->willReturn(2);
        $tag2->getName()->willReturn('Tag 2');

        $category1 = $this->prophesize(CategoryInterface::class);
        $category1->getId()->willReturn(3);
        $category2 = $this->prophesize(CategoryInterface::class);
        $category2->getId()->willReturn(4);

        $contentView->setSeoTitle('Seo Title');
        $contentView->setSeoDescription('Seo Description');
        $contentView->setSeoKeywords('Seo Keyword 1, Seo Keyword 2');
        $contentView->setSeoCanonicalUrl('https://caninical.localhost/');
        $contentView->setSeoNoIndex(true);
        $contentView->setSeoNoFollow(true);
        $contentView->setSeoHideInSitemap(true);

        $contentView->setExcerptTitle('Excerpt Title');
        $contentView->setExcerptDescription('Excerpt Description');
        $contentView->setExcerptMore('Excerpt More');
        $contentView->setExcerptImage(['id' => 8]);
        $contentView->setExcerptIcon(['id' => 9]);
        $contentView->setExcerptTags([$tag1->reveal(), $tag2->reveal()]);
        $contentView->setExcerptCategories([$category1->reveal(), $category2->reveal()]);

        $contentView->setTemplateKey('template-key');
        $contentView->setTemplateData(['someTemplate' => 'data']);

        $apiViewResolver = $this->createApiViewResolverInstance();

        $this->assertSame([
            'dimensionId' => '123-456',
            'excerptCategories' => [
                3,
                4,
            ],
            'excerptDescription' => 'Excerpt Description',
            'excerptIcon' => ['id' => 9],
            'excerptImage' => ['id' => 8],
            'excerptMore' => 'Excerpt More',
            'excerptTags' => [
                'Tag 1',
                'Tag 2',
            ],
            'excerptTitle' => 'Excerpt Title',
            'id' => 5,
            'published' => null,
            'publishedState' => false,
            'seoCanonicalUrl' => 'https://caninical.localhost/',
            'seoDescription' => 'Seo Description',
            'seoHideInSitemap' => true,
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoNoFollow' => true,
            'seoNoIndex' => true,
            'seoTitle' => 'Seo Title',
            'someTemplate' => 'data',
            'template' => 'template-key',
            'workflowPlace' => 'unpublished',
        ], $apiViewResolver->resolve($contentView));
    }
}
