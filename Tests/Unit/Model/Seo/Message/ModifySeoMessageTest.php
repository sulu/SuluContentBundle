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

namespace Sulu\Bundle\ContentBundle\Tests\Unit\Model\Seo\Message;

use PHPUnit\Framework\TestCase;
use Sulu\Bundle\ContentBundle\Common\Model\MissingResultException;
use Sulu\Bundle\ContentBundle\Model\Seo\Message\ModifySeoMessage;
use Sulu\Bundle\ContentBundle\Model\Seo\SeoViewInterface;

class ModifySeoMessageTest extends TestCase
{
    const RESOURCE_KEY = 'seo';

    public function testGetResourceKey(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals(self::RESOURCE_KEY, $message->getResourceKey());
    }

    public function testGetResourceId(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals('seo-1', $message->getResourceId());
    }

    public function testGetLocale(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals('en', $message->getLocale());
    }

    public function testGetTitle(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals('title-1', $message->getTitle());
    }

    public function testGetDescription(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals('description-1', $message->getDescription());
    }

    public function testGetKeywords(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertEquals('keywords-1', $message->getKeywords());
    }

    public function testGetCanonicalUrl(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertNull($message->getCanonicalUrl());
    }

    public function testGetNoIndex(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertFalse($message->getNoIndex());
    }

    public function testGetNoFollow(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertTrue($message->getNoFollow());
    }

    public function testGetHideInSitemap(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $this->assertNull($message->getHideInSitemap());
    }

    public function testGetSeo(): void
    {
        $this->expectException(MissingResultException::class);

        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $message->getSeo();
    }

    public function testSetSeo(): void
    {
        $message = new ModifySeoMessage(
            self::RESOURCE_KEY,
            'seo-1',
            'en',
            [
                'title' => 'title-1',
                'description' => 'description-1',
                'keywords' => 'keywords-1',
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => null,
            ]
        );

        $seo = $this->prophesize(SeoViewInterface::class);

        $this->assertEquals($message, $message->setSeo($seo->reveal()));
        $this->assertEquals($seo->reveal(), $message->getSeo());
    }
}
