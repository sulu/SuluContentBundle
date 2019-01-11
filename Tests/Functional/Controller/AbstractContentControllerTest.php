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
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\ContentTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionTrait;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AbstractContentControllerTest extends SuluTestCase
{
    use DimensionTrait;
    use ContentTrait;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();
    }

    public function testGet(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-contents/test-resource-1?locale=en');
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testPutWithPublishAction(): void
    {
        $this->createContent('test_resource_contents', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke('test-resource-1', 'en')->shouldBeCalled();

        $client = $this->createAuthenticatedClient();

        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_contents');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = ['template' => 'default', 'title' => 'title-1'];
        $client->request('PUT', '/api/test-resource-contents/test-resource-1?locale=en&action=publish', $payload);
        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
