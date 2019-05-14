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

namespace Sulu\Bundle\ContentBundle\Tests\Application\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Sulu\Bundle\ContentBundle\Controller\AbstractContentController;

/**
 * @Rest\RouteResource("test-resource-content")
 */
class TestResourceContentController extends AbstractContentController
{
    /**
     * @var TestControllerCallbackInterface
     */
    private $handlePublishCallback;

    /**
     * @var TestControllerCallbackInterface
     */
    private $handleDeleteCallback;

    protected function handlePublish(string $resourceId, string $locale): void
    {
        $this->handlePublishCallback->invoke($resourceId, $locale);
    }

    protected function handleDelete(string $resourceId, string $locale): void
    {
        $this->handleDeleteCallback->invoke($resourceId, $locale);
    }

    protected function getContentResourceKey(): string
    {
        return 'test_resource_contents';
    }

    public function setHandlePublishCallback(TestControllerCallbackInterface $handlePublishCallback): void
    {
        $this->handlePublishCallback = $handlePublishCallback;
    }

    public function setHandleDeleteCallback(TestControllerCallbackInterface $handleDeleteCallback): void
    {
        $this->handleDeleteCallback = $handleDeleteCallback;
    }
}
