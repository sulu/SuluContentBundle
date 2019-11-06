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

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ViewResolver\ApiViewResolverInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\AbstractContentView;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

trait ApiViewResolverTestCaseTrait
{
    abstract protected function createApiViewResolverInstance(): ApiViewResolverInterface;

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
        $contentView = new class() extends AbstractContentView implements ExcerptInterface, SeoInterface, TemplateInterface {
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;

            protected $id = 2;
            protected $dimensionId = '123-456';

            public function getContentId()
            {
                return 5;
            }
        };

        $tag1 = $this->prophesize(TagInterface::class);
        $tag1->getId()->willReturn(1);
        $tag2 = $this->prophesize(TagInterface::class);
        $tag2->getId()->willReturn(2);

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
        $contentView->setExcerptImage(8);
        $contentView->setExcerptIcon(9);
        $contentView->setExcerptTags([$tag1->reveal(), $tag2->reveal()]);
        $contentView->setExcerptCategories([$category1->reveal(), $category2->reveal()]);

        $contentView->setTemplateKey('template-key');
        $contentView->setTemplateData(['someTemplate' => 'data']);

        $apiViewResolver = $this->createApiViewResolverInstance();
        $this->assertSame([
            'dimensionId' => '123-456',
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptCategories' => [
                3,
                4,
            ],
            'excerptTags' => [
                1,
                2,
            ],
            'excerptImage' => 8,
            'excerptIcon' => 9,
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoCanonicalUrl' => 'https://caninical.localhost/',
            'seoNoIndex' => true,
            'seoNoFollow' => true,
            'seoHideInSitemap' => true,
            'id' => 5,
            'someTemplate' => 'data',
            'template' => 'template-key',
        ], $apiViewResolver->resolve($contentView));
    }
}
