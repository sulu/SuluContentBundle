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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoView;

class SeoViewTest extends TestCase
{
    const RESOURCE_KEY = 'test_resource_seos';

    public function testGetResourceKey(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame(self::RESOURCE_KEY, $seoView->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame('resource-1', $seoView->getResourceId());
    }

    public function testGetLocale(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame('en', $seoView->getLocale());
    }

    public function testGetTitle(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame('title-1', $seoView->getTitle());
    }

    public function testGetDescription(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame('description-1', $seoView->getDescription());
    }

    public function testGetKeywords(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertSame('keywords-1', $seoView->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertNull($seoView->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertFalse($seoView->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertTrue($seoView->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $this->assertFalse($seoView->getHideInSitemap());
    }

    public function testWithResource(): void
    {
        $seoView = new SeoView(
            self::RESOURCE_KEY,
            'resource-1',
            'en',
            'title-1',
            'description-1',
            'keywords-1',
            null,
            false,
            true,
            false
        );

        $result = $seoView->withResource(self::RESOURCE_KEY, '123-123-123', 'de');
        $this->assertNotSame($seoView, $result);
        $this->assertSame(self::RESOURCE_KEY, $result->getResourceKey());
        $this->assertSame('123-123-123', $result->getResourceId());
        $this->assertSame('de', $result->getLocale());

        $this->assertSame($seoView->getTitle(), $result->getTitle());
        $this->assertSame($seoView->getDescription(), $result->getDescription());
        $this->assertSame($seoView->getKeywords(), $result->getKeywords());
        $this->assertSame($seoView->getCanonicalUrl(), $result->getCanonicalUrl());
        $this->assertSame($seoView->getNoFollow(), $result->getNoFollow());
        $this->assertSame($seoView->getNoIndex(), $result->getNoIndex());
        $this->assertSame($seoView->getHideInSitemap(), $result->getHideInSitemap());
    }
}
