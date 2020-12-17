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
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapAlternateLink;
use Sulu\Bundle\WebsiteBundle\Sitemap\SitemapUrl;

class ContentSitemapProviderTest extends BaseTestCase
{
    const SCHEME = 'https';
    const HOST = 'localhost';

    use CreateExampleTrait;

    /**
     * @var ContentSitemapProvider
     */
    private $contentSitemapProvider;

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        // Example 1 (both locales, both published)
        $example1 = static::createExample(
            [
                'en' => [
                    'title' => 'example-1',
                    'published' => true,
                ],
                'de' => [
                    'title' => 'beispiel-1',
                    'published' => true,
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        // Example 2 (only en, published)
        $example2 = static::createExample(
            [
                'en' => [
                    'title' => 'example-2',
                    'published' => true,
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        // Example 3 (both locales, only en published)
        $example3 = static::createExample(
            [
                'en' => [
                    'title' => 'example-3',
                    'published' => true,
                ],
                'de' => [
                    'title' => 'beispiel-3',
                    'published' => false,
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        // Example 4 (only de, published)
        $example4 = static::createExample(
            [
                'de' => [
                    'title' => 'beispiel-4',
                    'published' => true,
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        // Example 5 (only en, not published)
        $example5 = static::createExample(
            [
                'en' => [
                    'title' => 'example-5',
                    'published' => false,
                ],
            ],
            [
                'create_route' => true,
            ]
        );

        static::getEntityManager()->flush();
    }

    protected function setUp(): void
    {
        $this->contentSitemapProvider = $this->getContainer()->get('example_test.example_sitemap_provider');
    }

    public function testBuild(): void
    {
        /** @var SitemapUrl[] $sitemapEntries */
        $sitemapEntries = $this->contentSitemapProvider->build(1, static::SCHEME, static::HOST);

        $sitemapEntries = $this->mapSitemapEntries($sitemapEntries);

        $this->assertArraySnapshot('sitemap.json', $sitemapEntries);
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
        usort(
            $sitemapEntries,
            function (SitemapUrl $a, SitemapUrl $b) {
                return strcmp($a->getLoc(), $b->getLoc());
            }
        );

        return array_map(
            function (SitemapUrl $sitemapUrl) {
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
            },
            $sitemapEntries
        );
    }
}
