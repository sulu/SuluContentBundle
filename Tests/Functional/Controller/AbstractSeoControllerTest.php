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

        $this->assertSame(
            [
                'title' => 'seo-title',
                'description' => 'seo-description',
                'keywords' => 'seo-keywords',
                'canonicalUrl' => 'seo-url',
                'noIndex' => false,
                'noFollow' => true,
                'hideInSitemap' => false,
            ],
            $result
        );
    }

    public function testGetAbsent(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-seos/absent-resource?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => null,
                'description' => null,
                'keywords' => null,
                'canonicalUrl' => null,
                'noIndex' => false,
                'noFollow' => false,
                'hideInSitemap' => false,
            ],
            $result
        );
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

        $this->assertSame(
            [
                'title' => 'new-title',
                'description' => 'new-description',
                'keywords' => 'new-keywords',
                'canonicalUrl' => 'new-url',
                'noIndex' => true,
                'noFollow' => false,
                'hideInSitemap' => true,
            ],
            $result
        );
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

        $this->assertSame(
            [
                'title' => 'new-title',
                'description' => 'new-description',
                'keywords' => 'new-keywords',
                'canonicalUrl' => 'new-url',
                'noIndex' => true,
                'noFollow' => false,
                'hideInSitemap' => true,
            ],
            $result
        );
    }

    public function testPutWithPublishAction(): void
    {
        $this->createDraftSeoDimension('test_resource_seos', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke('test-resource-1', 'en')->shouldBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $seoController = $container->get('sulu_content.controller.test_resource_seos');
            $seoController->setHandlePublishCallback($handlePublishCallback->reveal());
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

        $this->assertSame(
            [
                'title' => 'new-title',
                'description' => 'new-description',
                'keywords' => 'new-keywords',
                'canonicalUrl' => 'new-url',
                'noIndex' => true,
                'noFollow' => false,
                'hideInSitemap' => true,
            ],
            $result
        );
    }
}
