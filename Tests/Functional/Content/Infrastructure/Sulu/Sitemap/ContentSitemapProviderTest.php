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
use Sulu\Bundle\ContentBundle\Tests\Application\ExampleTestBundle\Entity\ExampleContentProjection;
use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Sulu\Bundle\ContentBundle\Tests\Traits\CreateExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\ModifyExampleTrait;
use Sulu\Bundle\ContentBundle\Tests\Traits\PublishExampleTrait;
use Sulu\Bundle\WebsiteBundle\Sitemap\Sitemap;
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

    /**
     * @var ExampleContentProjection
     */
    private static $example1en;

    /**
     * @var ExampleContentProjection
     */
    private static $example1de;

    /**
     * @var ExampleContentProjection
     */
    private static $example2en;

    /**
     * @var ExampleContentProjection
     */
    private static $example3en;

    /**
     * @var ExampleContentProjection
     */
    private static $example3de;

    /**
     * @var ExampleContentProjection
     */
    private static $example4de;

    public static function setUpBeforeClass(): void
    {
        static::purgeDatabase();
        parent::setUpBeforeClass();

        // Example 1 (both locales, both published)
        static::$example1en = static::createExample(['title' => 'example-1'], 'en');
        static::$example1en = static::publishExample(static::$example1en->getContentId(), 'en');

        static::$example1de = static::modifyExample(static::$example1en->getContentId(), ['title' => 'beispiel-1'], 'de');
        static::$example1de = static::publishExample(static::$example1de->getContentId(), 'de');

        // Example 2 (only en, published)
        static::$example2en = static::createExample(['title' => 'example-2'], 'en');
        static::$example2en = static::publishExample(static::$example2en->getContentId(), 'en');

        // Example 3 (both locales, only en published)
        static::$example3en = static::createExample(['title' => 'example-3'], 'en');
        static::$example3en = static::publishExample(static::$example3en->getContentId(), 'en');

        static::$example3de = static::modifyExample(static::$example3en->getContentId(), ['title' => 'beispiel-3'], 'de');

        // Example 4 (only de, published)
        static::$example4de = static::createExample(['title' => 'beispiel-4'], 'de');
        static::$example4de = static::publishExample(static::$example4de->getContentId(), 'de');

        // Example 5 (only en, not published)
        static::createExample(['title' => 'example-5'], 'en');
    }

    public function setUp(): void
    {
        $this->contentSitemapProvider = $this->getContainer()->get('example_test.example_sitemap_provider');
    }

    public function testBuild(): void
    {
        /** @var SitemapUrl[] $entries */
        $entries = $this->contentSitemapProvider->build(1, static::SCHEME, static::HOST);

        $this->assertIsArray($entries);
        $this->assertCount(4, $entries);

        foreach ($entries as $i => $entry) {
            $alternateLinks = $entry->getAlternateLinks();
            $this->assertIsArray($alternateLinks);

            switch ($i) {
                case 0:
                    $this->assertSame($this->getLocale(self::$example1de), $entry->getLocale());
                    $this->assertSame($this->getLocale(self::$example1de), $entry->getDefaultLocale());
                    $this->assertSame($this->getUrl(self::$example1de), $entry->getLoc());

                    $this->assertCount(2, $alternateLinks);
                    $this->assertSame($entry->getLocale(), $alternateLinks[$entry->getLocale()]->getLocale());
                    $this->assertSame($entry->getLoc(), $alternateLinks[$entry->getLocale()]->getHref());
                    $this->assertSame($this->getLocale(self::$example1en), $alternateLinks[$this->getLocale(self::$example1en)]->getLocale());
                    $this->assertSame($this->getUrl(self::$example1en), $alternateLinks[$this->getLocale(self::$example1en)]->getHref());

                    break;
                case 1:
                    $this->assertSame($this->getLocale(self::$example2en), $entry->getLocale());
                    $this->assertSame($this->getLocale(self::$example2en), $entry->getDefaultLocale());
                    $this->assertSame($this->getUrl(self::$example2en), $entry->getLoc());

                    $this->assertCount(1, $alternateLinks);
                    $this->assertSame($entry->getLocale(), $alternateLinks[$entry->getLocale()]->getLocale());
                    $this->assertSame($entry->getLoc(), $alternateLinks[$entry->getLocale()]->getHref());

                    break;
                case 2:
                    $this->assertSame($this->getLocale(self::$example3en), $entry->getLocale());
                    $this->assertSame($this->getLocale(self::$example3en), $entry->getDefaultLocale());
                    $this->assertSame($this->getUrl(self::$example3en), $entry->getLoc());

                    $this->assertCount(1, $alternateLinks);
                    $this->assertSame($entry->getLocale(), $alternateLinks[$entry->getLocale()]->getLocale());
                    $this->assertSame($entry->getLoc(), $alternateLinks[$entry->getLocale()]->getHref());

                    break;
                case 3:
                    $this->assertSame($this->getLocale(self::$example4de), $entry->getLocale());
                    $this->assertSame($this->getLocale(self::$example4de), $entry->getDefaultLocale());
                    $this->assertSame($this->getUrl(self::$example4de), $entry->getLoc());

                    $this->assertCount(1, $alternateLinks);
                    $this->assertSame($entry->getLocale(), $alternateLinks[$entry->getLocale()]->getLocale());
                    $this->assertSame($entry->getLoc(), $alternateLinks[$entry->getLocale()]->getHref());

                    break;
            }
        }
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

    private function getUrl(ExampleContentProjection $contentProjection): string
    {
        $locale = '';

        if ('en' === $this->getLocale($contentProjection)) {
            $locale = '/en';
        }

        return sprintf('%s://%s%s%s', self::SCHEME, self::HOST, $locale, $contentProjection->getTemplateData()['url']);
    }

    private function getLocale(ExampleContentProjection $contentProjection): string
    {
        $locale = $contentProjection->getDimension()->getLocale();

        if (null === $locale) {
            throw new \RuntimeException('Locale cannot be null!');
        }

        return $locale;
    }
}
