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
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\ContentDimensionTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AbstractContentControllerTest extends SuluTestCase
{
    use DimensionTrait;
    use ContentDimensionTrait;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();
    }

    public function testGet(): void
    {
        $this->createContentDimension(
            'test_resource_contents',
            'test-resource-1',
            'en',
            'default',
            ['title' => 'content-title', 'article' => 'content-article']
        );

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-contents/test-resource-1?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('default', $result['template']);
        $this->assertSame('content-title', $result['title']);
        $this->assertSame('content-article', $result['article']);
    }

    public function testGetAbsent(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-contents/absent-resource?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('default', $result['template']);
        $this->assertArrayNotHasKey('title', $result);
        $this->assertArrayNotHasKey('article', $result);
    }

    public function testPut(): void
    {
        $this->createContentDimension('test_resource_contents', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke()->shouldNotBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_contents');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = ['template' => 'default', 'title' => 'new-title', 'article' => 'new-article'];
        $client->request('PUT', '/api/test-resource-contents/test-resource-1?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('default', $result['template']);
        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-article', $result['article']);
    }

    public function testPutAbsent(): void
    {
        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke()->shouldNotBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_contents');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = ['template' => 'default', 'title' => 'new-title', 'article' => 'new-article'];
        $client->request('PUT', '/api/test-resource-contents/absent-resource?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('default', $result['template']);
        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-article', $result['article']);
    }

    public function testPutWithPublishAction(): void
    {
        $this->createContentDimension('test_resource_contents', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke('test-resource-1', 'en')->shouldBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_contents');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = ['template' => 'default', 'title' => 'new-title', 'article' => 'new-article'];
        $client->request('PUT', '/api/test-resource-contents/test-resource-1?locale=en&action=publish', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame('default', $result['template']);
        $this->assertSame('new-title', $result['title']);
        $this->assertSame('new-article', $result['article']);
    }
}
