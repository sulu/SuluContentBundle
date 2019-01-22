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

use Sulu\Bundle\CategoryBundle\Entity\CategoryInterface;
use Sulu\Bundle\ContentBundle\Tests\Application\Controller\HandlePublishCallbackInterface;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\CategoryTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\DimensionIdentifierTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\ExcerptDimensionTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\MediaTrait;
use Sulu\Bundle\ContentBundle\Tests\Functional\Traits\TagTrait;
use Sulu\Bundle\MediaBundle\Entity\MediaInterface;
use Sulu\Bundle\TagBundle\Tag\TagInterface;
use Sulu\Bundle\TestBundle\Testing\SuluTestCase;

class AbstractExcerptControllerTest extends SuluTestCase
{
    use DimensionIdentifierTrait;
    use ExcerptDimensionTrait;
    use CategoryTrait;
    use TagTrait;
    use MediaTrait;

    /**
     * @var CategoryInterface
     */
    private $category1;

    /**
     * @var TagInterface
     */
    private $tag1;

    /**
     * @var MediaInterface
     */
    private $media1;

    /**
     * @var MediaInterface
     */
    private $media2;

    public function setUp()
    {
        parent::setUp();

        $this->purgeDatabase();

        $collectionType = $this->createCollectionType('collection-type-1');
        $collection = $this->createCollection($collectionType);
        $mediaTpe = $this->createMediaType('media-type-1');

        $this->category1 = $this->createCategory();
        $this->tag1 = $this->createTag('tag-1');
        $this->media1 = $this->createMedia($mediaTpe, $collection);
        $this->media2 = $this->createMedia($mediaTpe, $collection);
    }

    public function testGet(): void
    {
        $this->createDraftExcerptDimension(
            'test_resource_excerpts',
            'test-resource-1',
            'en',
            'excerpt-title',
            'excerpt-more',
            'excerpt-description',
            [$this->category1],
            [$this->tag1],
            [$this->media1],
            [$this->media2]
        );

        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-excerpts/test-resource-1?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => 'excerpt-title',
                'more' => 'excerpt-more',
                'description' => 'excerpt-description',
                'categories' => [$this->category1->getId()],
                'tags' => [$this->tag1->getName()],
                'icons' => [
                    'ids' => [$this->media1->getId()],
                ],
                'images' => [
                    'ids' => [$this->media2->getId()],
                ],
            ],
            $result
        );
    }

    public function testGetAbsent(): void
    {
        $client = $this->createAuthenticatedClient();
        $client->request('GET', '/api/test-resource-excerpts/absent-resource?locale=en');

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => null,
                'more' => null,
                'description' => null,
                'categories' => [],
                'tags' => [],
                'icons' => [
                    'ids' => [],
                ],
                'images' => [
                    'ids' => [],
                ],
            ],
            $result
        );
    }

    public function testPut(): void
    {
        $this->createDraftExcerptDimension(
            'test_resource_excerpts',
            'test-resource-1',
            'en',
            'excerpt-title',
            'excerpt-more',
            'excerpt-description',
            [$this->category1],
            [$this->tag1],
            [$this->media1],
            [$this->media2]
        );

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke()->shouldNotBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $excerptController = $container->get('sulu_content.controller.test_resource_excerpts');
            $excerptController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'more' => 'new-more',
            'description' => null,
            'categories' => [],
            'tags' => [],
            'icons' => [
                'ids' => [],
            ],
            'images' => [
                'ids' => [],
            ],
        ];
        $client->request('PUT', '/api/test-resource-excerpts/test-resource-1?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => 'new-title',
                'more' => 'new-more',
                'description' => null,
                'categories' => [],
                'tags' => [],
                'icons' => [
                    'ids' => [],
                ],
                'images' => [
                    'ids' => [],
                ],
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
            $contentController = $container->get('sulu_content.controller.test_resource_excerpts');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'more' => 'new-more',
            'description' => null,
            'categories' => [],
            'tags' => [$this->tag1->getName()],
            'icons' => [
                'ids' => [],
            ],
            'images' => [
                'ids' => [$this->media1->getId()],
            ],
        ];
        $client->request('PUT', '/api/test-resource-excerpts/absent-resource?locale=en', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => 'new-title',
                'more' => 'new-more',
                'description' => null,
                'categories' => [],
                'tags' => [$this->tag1->getName()],
                'icons' => [
                    'ids' => [],
                ],
                'images' => [
                    'ids' => [$this->media1->getId()],
                ],
            ],
            $result
        );
    }

    public function testPutWithPublishAction(): void
    {
        $this->createDraftExcerptDimension('test_resource_excerpts', 'test-resource-1');

        $handlePublishCallback = $this->prophesize(HandlePublishCallbackInterface::class);
        $handlePublishCallback->invoke('test-resource-1', 'en')->shouldBeCalled();

        $client = $this->createAuthenticatedClient();
        $container = $client->getContainer();
        if ($container) {
            $contentController = $container->get('sulu_content.controller.test_resource_excerpts');
            $contentController->setHandlePublishCallback($handlePublishCallback->reveal());
        }

        $payload = [
            'title' => 'new-title',
            'more' => 'new-more',
            'description' => null,
            'categories' => [$this->category1->getId()],
            'tags' => [$this->tag1->getName()],
            'icons' => [
                'ids' => [$this->media1->getId()],
            ],
            'images' => [
                'ids' => [$this->media2->getId()],
            ],
        ];
        $client->request('PUT', '/api/test-resource-excerpts/test-resource-1?locale=en&action=publish', $payload);

        $response = $client->getResponse();
        $result = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(
            [
                'title' => 'new-title',
                'more' => 'new-more',
                'description' => null,
                'categories' => [$this->category1->getId()],
                'tags' => [$this->tag1->getName()],
                'icons' => [
                    'ids' => [$this->media1->getId()],
                ],
                'images' => [
                    'ids' => [$this->media2->getId()],
                ],
            ],
            $result
        );
    }
}
