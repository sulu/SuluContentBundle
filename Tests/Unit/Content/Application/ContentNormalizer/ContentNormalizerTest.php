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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Content\Application\ContentNormalizer;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\ContentNormalizer;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\ContentNormalizerInterface;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Enhancer\ExcerptNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Enhancer\TemplateNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Application\ContentNormalizer\Enhancer\WorkflowNormalizeEnhancer;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ContentProjectionTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\ExcerptTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\SeoTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\TemplateTrait;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowInterface;
use Sulu\Bundle\ContentBundle\Content\Domain\Model\WorkflowTrait;
use Sulu\Bundle\TagBundle\Tag\TagInterface;

class ContentNormalizerTest extends TestCase
{
    protected function createContentNormalizerInstance(): ContentNormalizerInterface
    {
        return new ContentNormalizer([
            new ExcerptNormalizeEnhancer(),
            new TemplateNormalizeEnhancer(),
            new WorkflowNormalizeEnhancer(),
        ]);
    }

    public function testResolveSimple(): void
    {
        $contentProjection = new class() implements ContentProjectionInterface {
            use ContentProjectionTrait;

            protected $id = 2;

            public function __construct()
            {
                $this->dimensionId = '123-456';
            }

            public function getContentId()
            {
                return 5;
            }
        };

        $apiViewResolver = $this->createContentNormalizerInstance();
        $this->assertSame([
            'dimensionId' => '123-456',
            'id' => 5,
        ], $apiViewResolver->normalize($contentProjection));
    }

    public function testResolveFull(): void
    {
        $contentProjection = new class() implements ContentProjectionInterface, ExcerptInterface, SeoInterface, TemplateInterface, WorkflowInterface {
            use ContentProjectionTrait;
            use ExcerptTrait;
            use SeoTrait;
            use TemplateTrait;
            use WorkflowTrait;

            protected $id = 2;

            public function __construct()
            {
                $this->dimensionId = '123-456';
            }

            public function getContentId()
            {
                return 5;
            }

            public static function getTemplateType(): string
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

        $contentProjection->setSeoTitle('Seo Title');
        $contentProjection->setSeoDescription('Seo Description');
        $contentProjection->setSeoKeywords('Seo Keyword 1, Seo Keyword 2');
        $contentProjection->setSeoCanonicalUrl('https://caninical.localhost/');
        $contentProjection->setSeoNoIndex(true);
        $contentProjection->setSeoNoFollow(true);
        $contentProjection->setSeoHideInSitemap(true);

        $contentProjection->setExcerptTitle('Excerpt Title');
        $contentProjection->setExcerptDescription('Excerpt Description');
        $contentProjection->setExcerptMore('Excerpt More');
        $contentProjection->setExcerptImage(['id' => 8]);
        $contentProjection->setExcerptIcon(['id' => 9]);
        $contentProjection->setExcerptTags([$tag1->reveal(), $tag2->reveal()]);
        $contentProjection->setExcerptCategories([$category1->reveal(), $category2->reveal()]);

        $contentProjection->setTemplateKey('template-key');
        $contentProjection->setTemplateData(['someTemplate' => 'data']);

        $apiViewResolver = $this->createContentNormalizerInstance();

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
            'workflowName' => 'content_workflow',
            'workflowPlace' => 'unpublished',
        ], $apiViewResolver->normalize($contentProjection));
    }
}
