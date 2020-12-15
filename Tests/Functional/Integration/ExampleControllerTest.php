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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Integration;

use Sulu\Bundle\ContentBundle\Tests\Functional\BaseTestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * The integration test should have no impact on the coverage so we set it to coversNothing.
 *
 * @coversNothing
 */
class ExampleControllerTest extends BaseTestCase
{
    /**
     * @var KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->createAuthenticatedClient(
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json']
        );
    }

    public function testPostPublish(): int
    {
        self::purgeDatabase();
        self::initPhpcr();

        $this->client->request('POST', '/admin/api/examples?locale=en&action=publish', [], [], [], json_encode([
            'template' => 'example-2',
            'title' => 'Test Example',
            'url' => '/my-example',
            'published' => '2020-05-08T00:00:00+00:00', // Should be ignored
            'images' => null,
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoCanonicalUrl' => 'https://sulu.io/',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoNoIndex' => true,
            'seoNoFollow' => true,
            'seoHideInSitemap' => true,
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [],
            'excerptIcon' => null,
            'excerptMedia' => null,
        ]) ?: null);

        $response = $this->client->getResponse();
        $content = json_decode((string) $response->getContent(), true);
        $id = $content['id'] ?? null;

        $this->assertResponseSnapshot('example_post_publish.json', $response, 201);
        $this->assertNotSame('2020-05-08T00:00:00+00:00', $content['published']);

        self::ensureKernelShutdown();

        $websiteClient = $this->createWebsiteClient();
        $websiteClient->request('GET', '/en/my-example');

        $response = $websiteClient->getResponse();
        $this->assertHttpStatusCode(200, $response);
        $content = $response->getContent();
        $this->assertIsString($content);
        $this->assertStringContainsString('EXAMPLE 2 TEMPLATE', $content);

        return $id;
    }

    /**
     * @depends testPostPublish
     */
    public function testPostTriggerUnpublish(int $id): void
    {
        $this->client->request('POST', '/admin/api/examples/' . $id . '?locale=en&action=unpublish');

        $response = $this->client->getResponse();

        $this->assertResponseSnapshot('example_post_trigger_unpublish.json', $response, 200);

        self::ensureKernelShutdown();

        $websiteClient = $this->createWebsiteClient();
        $websiteClient->request('GET', '/en/my-example');

        $response = $websiteClient->getResponse();
        $this->assertHttpStatusCode(404, $response);
    }

    public function testPost(): int
    {
        self::purgeDatabase();

        $this->client->request('POST', '/admin/api/examples?locale=en', [], [], [], json_encode([
            'template' => 'example-2',
            'title' => 'Test Example',
            'url' => '/my-example',
            'images' => null,
            'seoTitle' => 'Seo Title',
            'seoDescription' => 'Seo Description',
            'seoCanonicalUrl' => 'https://sulu.io/',
            'seoKeywords' => 'Seo Keyword 1, Seo Keyword 2',
            'seoNoIndex' => true,
            'seoNoFollow' => true,
            'seoHideInSitemap' => true,
            'excerptTitle' => 'Excerpt Title',
            'excerptDescription' => 'Excerpt Description',
            'excerptMore' => 'Excerpt More',
            'excerptTags' => ['Tag 1', 'Tag 2'],
            'excerptCategories' => [],
            'excerptIcon' => null,
            'excerptMedia' => null,
        ]) ?: null);

        $response = $this->client->getResponse();

        $this->assertResponseSnapshot('example_post.json', $response, 201);

        $id = json_decode((string) $response->getContent(), true)['id'] ?? null;

        return $id;
    }

    /**
     * @depends testPost
     */
    public function testGet(int $id): void
    {
        $this->client->request('GET', '/admin/api/examples/' . $id . '?locale=en');
        $response = $this->client->getResponse();
        $this->assertResponseSnapshot('example_get.json', $response, 200);

        self::ensureKernelShutdown();

        $websiteClient = $this->createWebsiteClient();
        $websiteClient->request('GET', '/en/my-example');

        $response = $websiteClient->getResponse();
        $this->assertHttpStatusCode(404, $response);
    }

    /**
     * @depends testPost
     * @depends testGet
     */
    public function testPut(int $id): void
    {
        $this->client->request('PUT', '/admin/api/examples/' . $id . '?locale=en', [], [], [], json_encode([
            'template' => 'default',
            'title' => 'Test Example 2',
            'url' => '/my-example-2',
            'article' => '<p>Test Article 2</p>',
            'seoTitle' => 'Seo Title 2',
            'seoDescription' => 'Seo Description 2',
            'seoCanonicalUrl' => 'https://sulu.io/2',
            'seoKeywords' => 'Seo Keyword 3, Seo Keyword 4',
            'seoNoIndex' => false,
            'seoNoFollow' => false,
            'seoHideInSitemap' => false,
            'excerptTitle' => 'Excerpt Title 2',
            'excerptDescription' => 'Excerpt Description 2',
            'excerptMore' => 'Excerpt More 2',
            'excerptTags' => ['Tag 3', 'Tag 4'],
            'excerptCategories' => [],
            'excerptIcon' => null,
            'excerptMedia' => null,
        ]) ?: null);

        $response = $this->client->getResponse();

        $this->assertResponseSnapshot('example_put.json', $response, 200);
    }

    /**
     * @depends testPost
     * @depends testPut
     */
    public function testGetList(): void
    {
        $this->client->request('GET', '/admin/api/examples?locale=en');
        $response = $this->client->getResponse();

        $this->assertResponseSnapshot('example_cget.json', $response, 200);
    }

    /**
     * @depends testPost
     * @depends testGetList
     */
    public function testDelete(int $id): void
    {
        $this->client->request('DELETE', '/admin/api/examples/' . $id . '?locale=en');
        $response = $this->client->getResponse();
        $this->assertHttpStatusCode(204, $response);
    }

    protected function getSnapshotFolder(): string
    {
        return 'responses';
    }
}
