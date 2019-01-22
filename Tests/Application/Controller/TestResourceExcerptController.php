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
use Sulu\Bundle\ContentBundle\Controller\AbstractExcerptController;

/**
 * @Rest\RouteResource("test-resource-excerpt")
 */
class TestResourceExcerptController extends AbstractExcerptController
{
    /**
     * @var HandlePublishCallbackInterface
     */
    private $handlePublishCallback;

    protected function handlePublish(string $resourceId, string $locale): void
    {
        $this->handlePublishCallback->invoke($resourceId, $locale);
    }

    protected function getExcerptResourceKey(): string
    {
        return 'test_resource_excerpts';
    }

    public function setHandlePublishCallback(HandlePublishCallbackInterface $handlePublishCallback): void
    {
        $this->handlePublishCallback = $handlePublishCallback;
    }
}
