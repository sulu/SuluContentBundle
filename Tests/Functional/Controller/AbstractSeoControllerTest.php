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

namespace Sulu\Bundle\ContentBundle\Tests\Functional\Controller;

use Sulu\Bundle\ContentBundle\Tests\Application\Controller\HandlePublishCallbackInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionIdentifierTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\SeoDimensionTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AbstractSeoControllerTest extends SuluTestCase
{
    use DimensionIdentifierTrait;
    use SeoDimensionTrait;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();
    }

    public function testGet(): void
    {
        $this->createDraftSeoDimension(
            'test_resource_seos',
            'test-resource-1',
            'en',
            'seo-title',
            'seo-description',
            'seo-keywords',
            'seo-url',
            false,
            true,
            false
        );

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-seos/test-resource-1?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('seo-title', $result['title']);
        $this->assertSame('seo-description', $result['description']);
        $this->assertSame('seo-keywords', $result['keywords']);
        $this->assertSame('seo-url', $result['canonicalUrl']);
        $this->assertFalse($result['noIndex']);
        $this->assertTrue($result['noFollow']);
        $this->assertFalse($result['hideInSitemap']);
    }

    public function testGetAbsent(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-seos/absent-resource?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertNull($result['title']);
        $this->assertNull($result['description']);
        $this->assertNull($result['keywords']);
        $this->assertNull($result['canonicalUrl']);
        $this->assertFalse($result['noIndex']);
        $this->assertFalse($result['noFollow']);
        $this->assertFalse($result['hideInSitemap']);
    }

    public function testPut(): void
    {
        $this->createDraftSeoDimension('test_resource_seos', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke()->shouldNotBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_seos');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'description' => 'new-description',
            'keywords' => 'new-keywords',
            'canonicalUrl' => 'new-url',
            'noIndex' => true,
            'noFollow' => false,
            'hideInSitemap' => true,
        ];
        $client->request('PUT', '/api/test-resource-seos/test-resource-1?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-description', $result['description']);
        $this->assertSame('new-keywords', $result['keywords']);
        $this->assertSame('new-url', $result['canonicalUrl']);
        $this->assertTrue($result['noIndex']);
        $this->assertFalse($result['noFollow']);
        $this->assertTrue($result['hideInSitemap']);
    }

    public function testPutAbsent(): void
    {
        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke()->shouldNotBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_seos');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'description' => 'new-description',
            'keywords' => 'new-keywords',
            'canonicalUrl' => 'new-url',
            'noIndex' => true,
            'noFollow' => false,
            'hideInSitemap' => true,
        ];
        $client->request('PUT', '/api/test-resource-seos/absent-resource?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-description', $result['description']);
        $this->assertSame('new-keywords', $result['keywords']);
        $this->assertSame('new-url', $result['canonicalUrl']);
        $this->assertTrue($result['noIndex']);
        $this->assertFalse($result['noFollow']);
        $this->assertTrue($result['hideInSitemap']);
    }

    public function testPutWithPublishAction(): void
    {
        $this->createDraftSeoDimension('test_resource_seos', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke('test-resource-1', 'en')->shouldBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_seos');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'description' => 'new-description',
            'keywords' => 'new-keywords',
            'canonicalUrl' => 'new-url',
            'noIndex' => true,
            'noFollow' => false,
            'hideInSitemap' => true,
        ];
        $client->request('PUT', '/api/test-resource-seos/test-resource-1?locale=en&action=publish', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-description', $result['description']);
        $this->assertSame('new-keywords', $result['keywords']);
        $this->assertSame('new-url', $result['canonicalUrl']);
        $this->assertTrue($result['noIndex']);
        $this->assertFalse($result['noFollow']);
        $this->assertTrue($result['hideInSitemap']);
    }
}
