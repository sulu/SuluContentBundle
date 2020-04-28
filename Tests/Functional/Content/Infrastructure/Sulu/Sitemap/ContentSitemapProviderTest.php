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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Content\Infrastructure\Sulu\Sitemap;

use Sulu\Bundle\ContentBundle\Content\Infrastructure\Sulu\Sitemap\ContentSitemapProvider;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\PublishExampleTrait;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;

class ContentSitemapProviderTest extends BaseTestCase
{
    const SCHEME = 'https';
    const HOST = 'localhost';

    use CreateExampleTrait;
    use ModifyExampleTrait;
    use PublishExampleTrait;

    /**
     * @var ContentSitemapProvider
     */
    private $contentSitemapProvider;

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        // Example 1 (both locales, both published)
        $example1en = static::createExample(['title' => 'example-1'], 'en');
        $example1en = static::publishExample($example1en->getContentId(), 'en');

        $example1de = static::modifyExample($example1en->getContentId(), ['title' => 'beispiel-1'], 'de');
        $example1de = static::publishExample($example1de->getContentId(), 'de');

        // Example 2 (only en, published)
        $example2en = static::createExample(['title' => 'example-2'], 'en');
        $example2en = static::publishExample($example2en->getContentId(), 'en');

        // Example 3 (both locales, only en published)
        $example3en = static::createExample(['title' => 'example-3'], 'en');
        $example3en = static::publishExample($example3en->getContentId(), 'en');

        $example3de = static::modifyExample($example3en->getContentId(), ['title' => 'beispiel-3'], 'de');

        // Example 4 (only de, published)
        $example4de = static::createExample(['title' => 'beispiel-4'], 'de');
        $example4de = static::publishExample($example4de->getContentId(), 'de');

        // Example 5 (only en, not published)
        $example5en = static::createExample(['title' => 'example-5'], 'en');
    }

    public function setUp(): void
    {
        $this->contentSitemapProvider = $this->getContainer()->get('example_test.example_sitemap_provider');
    }

    public function testBuild(): void
    {
        /** @var SitemapUrl[] $sitemapEntries */
        $sitemapEntries = $this->contentSitemapProvider->build(1, static::SCHEME, static::HOST);

        $sitemapEntries = $this->mapSitemapEntries($sitemapEntries);

        $this->assertContent('sitemap.json', json_encode($sitemapEntries) ?: '');
    }

    public function testCreateSitemap(): void
    {
        $sitemap = $this->contentSitemapProvider->createSitemap(static::SCHEME, static::HOST);

        $this->assertNotNull($sitemap);
        $this->assertSame(Sitemap::class, \get_class($sitemap));
        $this->assertSame($this->contentSitemapProvider->getAlias(), $sitemap->getAlias());
        $this->assertSame($this->contentSitemapProvider->getMaxPage(static::SCHEME, static::HOST), $sitemap->getMaxPage());
    }

    public function testGetMaxPage(): void
    {
        $this->assertSame(1, $this->contentSitemapProvider->getMaxPage(static::SCHEME, static::HOST));
    }

    public function testGetAlias(): void
    {
        $this->assertSame('examples', $this->contentSitemapProvider->getAlias());
    }

    /**
     * @param SitemapUrl[] $sitemapEntries
     *
     * @return array<string, mixed>
     */
    private function mapSitemapEntries(array $sitemapEntries): array
    {
        return array_map(function (SitemapUrl $sitemapUrl) {
            return [
                'locale' => $sitemapUrl->getLocale(),
                'defaultLocale' => $sitemapUrl->getDefaultLocale(),
                'loc' => $sitemapUrl->getLoc(),
                'alternateLinks' => array_map(function (SitemapAlternateLink $alternateLink) {
                    return [
                        'locale' => $alternateLink->getLocale(),
                        'href' => $alternateLink->getHref(),
                    ];
                }, $sitemapUrl->getAlternateLinks()),
            ];
        }, $sitemapEntries);
    }

    protected function getResponseContentFolder(): string
    {
        return 'snapshots';
    }
}
