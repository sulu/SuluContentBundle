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

/**
 * The integration test should have no impact on the coverage so we set it to coversNothing.
 *
 * @coversNothing
 */
class ExampleControllerTest extends BaseTestCase
{
    protected $client;

    public static function setUpBeforeClass(): void
    {
        self::purgeDatabase();
    }

    public function setUp(): void
    {
        $this->client = $this->createAuthenticatedClient();
    }

    public function testPost(): int
    {
        $this->client->request('POST', '/admin/api/examples?locale=en', [
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
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseContent('example_post.json', $response, 201);

        $id = json_decode($response->getContent(), true)['id'] ?? null;

        return $id;
    }

    /**
     * @depends testPost
     */
    public function testGet(int $id): void
    {
        $this->client->request('GET', '/admin/api/examples/' . $id . '?locale=en');
        $response = $this->client->getResponse();
        $this->assertResponseContent('example_get.json', $response, 200);
    }

    /**
     * @depends testPost
     * @depends testGet
     */
    public function testPut(int $id): void
    {
        $this->client->request('PUT', '/admin/api/examples/' . $id . '?locale=en', [
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
        ]);

        $response = $this->client->getResponse();

        $this->assertResponseContent('example_put.json', $response, 200);
    }

    /**
     * @depends testPost
     * @depends testPut
     */
    public function testGetList(): void
    {
        $this->client->request('GET', '/admin/api/examples?locale=en');
        $response = $this->client->getResponse();

        $this->assertResponseContent('example_cget.json', $response, 200);
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
}
